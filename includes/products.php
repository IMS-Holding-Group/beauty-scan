<?php
declare(strict_types=1);

function allow_lookup(int $userId): bool {
 $path = app_root() . '/data/lookup_rate.json';
 $dir = dirname($path);
 if (!is_dir($dir)) { mkdir($dir, 0700, true); }
 $handle = fopen($path, 'c+');
 if (!$handle) { return false; }
 flock($handle, LOCK_EX);
 $raw = stream_get_contents($handle);
 $data = json_decode($raw ?: '{}', true);
 if (!is_array($data)) { $data = []; }
 $now = time();
 $key = (string)$userId;
 $kept = [];
 foreach ($data[$key] ?? [] as $stamp) {
  if ($now - (int)$stamp < 600) { $kept[] = (int)$stamp; } }
 if (count($kept) >= 30) {
  $data[$key] = $kept;
  rewind($handle);
  ftruncate($handle, 0);
  fwrite($handle, json_encode($data));
  flock($handle, LOCK_UN);
  fclose($handle);
  return false; }
 $kept[] = $now;
 $data[$key] = $kept;
 rewind($handle);
 ftruncate($handle, 0);
 fwrite($handle, json_encode($data));
 flock($handle, LOCK_UN);
 fclose($handle);
 return true; }

function http_get(string $url): ?string {
 $context = stream_context_create([
  'http' => [
   'method' => 'GET',
   'timeout' => 6,
   'header' => "User-Agent: BeautyScan STEM (educational; local use)\r\nAccept: application/json\r\n",
  ],
  'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
 ]);
 $raw = @file_get_contents($url, false, $context, 0, 1000001);
 if (is_string($raw)) { return strlen($raw) > 1000000 ? null : $raw; }
 if (!function_exists('curl_init')) { return null; }
 $ch = curl_init($url);
 if ($ch === false) { return null; }
 curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT => 6,
  CURLOPT_CONNECTTIMEOUT => 6,
  CURLOPT_USERAGENT => 'BeautyScan STEM (educational; local use)',
  CURLOPT_HTTPHEADER => ['Accept: application/json'],
 ]);
 $raw = curl_exec($ch);
 curl_close($ch);
 if (!is_string($raw) || strlen($raw) > 1000000) { return null; }
 return $raw; }

function fetch_obf(string $barcode): array {
 $base = rtrim(env('OBF_API_BASE'), '/');
 if ($base === '' || !ctype_digit($barcode)) { return ['status' => 'network', 'product' => null]; }
 $raw = http_get($base . '/product/' . $barcode . '.json');
 if ($raw === null) { return ['status' => 'network', 'product' => null]; }
 $data = json_decode($raw, true);
 if (!is_array($data) || (int)($data['status'] ?? 0) !== 1 || !is_array($data['product'] ?? null)) { return ['status' => 'missing', 'product' => null]; }
 $item = $data['product'];
 $name = trim((string)($item['product_name_ar'] ?? $item['product_name'] ?? ''));
 $brand = trim((string)($item['brands'] ?? ''));
 $category = trim((string)($item['categories'] ?? ''));
 $ingredients = trim((string)($item['ingredients_text_ar'] ?? $item['ingredients_text'] ?? $item['ingredients_text_en'] ?? ''));
 return ['status' => 'ok', 'product' => [
  'name' => mb_substr($name, 0, 300),
  'brand' => mb_substr($brand, 0, 200),
  'category' => mb_substr($category, 0, 200),
  'ingredients_text' => mb_substr($ingredients, 0, 8000),
 ]]; }

function judge_product(bool $valid, ?array $product): array {
 if (!$valid) { return ['needs', ['رقم المراقبة في الباركود غير صحيح']]; }
 if (!$product) { return ['needs', ['المنتج غير موجود في السجلات المحلية ولا في المصدر الخارجي']]; }
 $reasons = [];
 if (trim((string)($product['name'] ?? '')) === '') { $reasons[] = 'اسم المنتج غير مكتمل'; }
 if (trim((string)($product['brand'] ?? '')) === '') { $reasons[] = 'العلامة التجارية غير مكتملة'; }
 if (trim((string)($product['ingredients_text'] ?? '')) === '') { $reasons[] = 'قائمة المكونات غير مكتملة'; }
 $complete = $reasons === [];
 $verified = (int)($product['is_verified'] ?? 0) === 1;
 $fromObf = (string)($product['source'] ?? '') === 'open_beauty_facts';
 if ($complete && ($verified || $fromObf)) {
  if ($verified) { $reasons[] = 'الباركود صحيح والمنتج موثّق في السجلات المحلية مع بيانات مكتملة'; }
  else { $reasons[] = 'الباركود صحيح وبيانات الاسم والعلامة والمكونات مكتملة في المصدر الخارجي'; }
  return ['trusted', $reasons]; }
 if ($complete) { $reasons[] = 'المنتج موجود لكن سجله المحلي غير موثّق'; }
 return ['needs', $reasons]; }

function catalog_all(): array {
 return db_all('SELECT id, inci_name, arabic_name, function_ar, notes_ar, synonyms FROM ingredients ORDER BY arabic_name'); }

function product_photo(string $barcode): string {
 $code = preg_replace('/\D/', '', $barcode) ?? '';
 if ($code === '' || !is_file(app_root() . '/assets/images/products/' . $code . '.jpg')) { return ''; }
 return '/assets/images/products/' . $code . '.jpg'; }

function ensure_product(string $barcode, int $userId): array {
 $code = clean_barcode($barcode);
 if ($code !== '' && !in_array(strlen($code), [8, 12, 13], true)) {
  return [
   'barcode' => $code,
   'valid' => false,
   'verdict' => 'needs',
   'verdict_text' => 'بيانات المنتج تحتاج إلى التحقق',
   'reasons' => ['طول الباركود غير مدعوم. الصيغ المدعومة هي EAN-13 وEAN-8 وUPC-A وUPC-E'],
   'product' => null,
   'explanations' => [],
   'allergy' => ['status' => 'missing', 'names' => []],
  ]; }
 $valid = is_valid_barcode($code);
 $product = null;
 $network = false;
 $limited = false;
 if ($code !== '' && ctype_digit($code)) {
  $product = db_one('SELECT * FROM products WHERE barcode = ?', [$code]);
  if (!$product && $valid) {
   if (!allow_lookup($userId)) { $limited = true; }
   else {
    $fetched = fetch_obf($code);
    if ($fetched['status'] === 'ok' && is_array($fetched['product'])) {
     $item = $fetched['product'];
     db_insert('INSERT INTO products (barcode, name, brand, category, ingredients_text, source, is_verified, fetched_at) VALUES (?, ?, ?, ?, ?, ?, 0, ?)', [
      $code, $item['name'], $item['brand'], $item['category'], $item['ingredients_text'], 'open_beauty_facts', now_iso(),
     ]);
     $product = db_one('SELECT * FROM products WHERE barcode = ?', [$code]); }
    elseif ($fetched['status'] === 'network') { $network = true; } } } }
 [$verdict, $reasons] = judge_product($valid, $product);
 if ($network) { $reasons[] = 'تعذر الوصول إلى المصدر الخارجي، وتم الاعتماد على البيانات المحلية'; }
 if ($limited) { $reasons[] = 'تعذر إتمام البحث حالياً. حاول بعد قليل'; }
 $text = trim((string)($product['ingredients_text'] ?? ''));
 $catalog = catalog_all();
 $allergens = db_all('SELECT id, name, normalized_name, ingredient_id FROM user_allergens WHERE user_id = ?', [$userId]);
 return [
  'barcode' => $code,
  'valid' => $valid,
  'verdict' => $verdict,
  'verdict_text' => $verdict === 'trusted' ? 'بيانات المنتج موثوقة' : 'بيانات المنتج تحتاج إلى التحقق',
  'reasons' => $reasons,
  'product' => $product,
  'explanations' => $text === '' ? [] : explain_ingredients($text, $catalog),
  'allergy' => match_allergens($text, $allergens, $catalog),
 ]; }
