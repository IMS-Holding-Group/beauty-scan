<?php
declare(strict_types=1);

function day_input(string $value, bool $required): ?string {
 $value = trim($value);
 if ($value === '') {
  if ($required) { throw new InvalidArgumentException('day'); }
  return null; }
 $dt = parse_iso_day($value);
 if (!$dt) { throw new InvalidArgumentException('day'); }
 $year = (int)$dt->format('Y');
 if ($year < 2000 || $year > 2100) { throw new InvalidArgumentException('day'); }
 return $dt->format('Y-m-d'); }

function decorate_product(array $row, int $noticeDays): array {
 $pao = $row['pao_months'] === null || $row['pao_months'] === '' ? null : (int)$row['pao_months'];
 [$effective, $reason] = effective_expiry((string)$row['expiry_date'], $row['opened_date'] ?: null, $pao);
 [$status, $days] = status_for($effective, $noticeDays, today_riyadh());
 $row['effective'] = $effective;
 $row['reason'] = $reason;
 $row['reason_text'] = reason_text($reason);
 $row['status'] = $status;
 $row['status_text'] = status_label($status);
 $row['days'] = $days;
 $row['parts'] = date_parts($effective);
 $row['expiry_parts'] = date_parts((string)$row['expiry_date']);
 $row['opened_parts'] = $row['opened_date'] ? date_parts((string)$row['opened_date']) : null;
 return $row; }

function list_user_products(int $userId, int $noticeDays): array {
 $rows = db_all('SELECT * FROM user_products WHERE user_id = ? ORDER BY effective_expiry_date ASC, id DESC', [$userId]);
 $out = [];
 foreach ($rows as $row) { $out[] = decorate_product($row, $noticeDays); }
 return $out; }

function user_product(int $userId, int $id): ?array {
 $row = db_one('SELECT * FROM user_products WHERE id = ? AND user_id = ?', [$id, $userId]);
 if (!$row) { return null; }
 $user = current_user();
 return decorate_product($row, (int)($user['notice_days'] ?? 30)); }

function save_user_product(int $userId, array $input, ?int $id): array {
 try {
  $expiry = day_input((string)($input['expiry_date'] ?? ''), true);
  $opened = day_input((string)($input['opened_date'] ?? ''), false); }
 catch (InvalidArgumentException $error) { return [false, 'تحقق من التواريخ']; }
 if ($opened && $opened > today_riyadh()->format('Y-m-d')) { return [false, 'تاريخ الفتح لا يكون في المستقبل']; }
 $paoRaw = trim((string)($input['pao_months'] ?? ''));
 $pao = null;
 if ($paoRaw !== '') {
  if (!ctype_digit($paoRaw) || (int)$paoRaw < 1 || (int)$paoRaw > 60) { return [false, 'مدة الاستخدام بعد الفتح بين 1 و 60 شهراً']; }
  $pao = (int)$paoRaw; }
 $name = trim((string)($input['custom_name'] ?? ''));
 if ($name === '' || mb_strlen($name, 'UTF-8') > 120) { return [false, 'اكتب اسماً للمنتج']; }
 $notes = trim((string)($input['notes'] ?? ''));
 if (mb_strlen($notes, 'UTF-8') > 500) { return [false, 'الملاحظات طويلة']; }
 $barcode = clean_barcode((string)($input['barcode'] ?? ''));
 if ($barcode !== '' && !is_valid_barcode($barcode)) { return [false, 'الباركود غير صالح']; }
 $productId = null;
 if ($barcode !== '') {
  $product = db_one('SELECT id, name FROM products WHERE barcode = ?', [$barcode]);
  if ($product) { $productId = (int)$product['id']; } }
 [$effective] = effective_expiry($expiry, $opened, $pao);
 if ($id) {
  $owned = db_one('SELECT id FROM user_products WHERE id = ? AND user_id = ?', [$id, $userId]);
  if (!$owned) { return [false, 'المنتج غير موجود']; }
  db_exec('UPDATE user_products SET custom_name = ?, expiry_date = ?, opened_date = ?, pao_months = ?, effective_expiry_date = ?, notes = ? WHERE id = ? AND user_id = ?', [
   $name, $expiry, $opened, $pao, $effective, $notes === '' ? null : $notes, $id, $userId,
  ]);
  return [true, '/products.php']; }
 db_insert('INSERT INTO user_products (user_id, product_id, custom_name, barcode, expiry_date, opened_date, pao_months, effective_expiry_date, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
  $userId, $productId, $name, $barcode === '' ? null : $barcode, $expiry, $opened, $pao, $effective, $notes === '' ? null : $notes, now_iso(),
 ]);
 return [true, '/products.php']; }

function delete_user_product(int $userId, int $id): bool {
 return db_exec('DELETE FROM user_products WHERE id = ? AND user_id = ?', [$id, $userId]) > 0; }

function status_counts(array $rows): array {
 $counts = ['ok' => 0, 'soon' => 0, 'week' => 0, 'expired' => 0];
 foreach ($rows as $row) {
  $status = (string)$row['status'];
  if (isset($counts[$status])) { $counts[$status] += 1; } }
 return $counts; }
