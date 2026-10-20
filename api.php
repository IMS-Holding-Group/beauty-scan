<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$action = (string)($_GET['action'] ?? '');
$method = (string)($_SERVER['REQUEST_METHOD'] ?? 'GET');
$body = $method === 'POST' ? json_body() : [];

if ($method === 'POST' && !csrf_ok()) { json_out(['ok' => false, 'message' => 'تعذر تنفيذ الطلب'], 403); }

function api_user(bool $ready): array {
 $user = current_user();
 if (!$user) { json_out(['ok' => false, 'message' => 'يلزم تسجيل الدخول'], 401); }
 if ($ready && (int)$user['onboarding_done'] !== 1) { json_out(['ok' => false, 'message' => 'أكمل إعداد الحساسية أولاً'], 403); }
 return $user; }

if ($action === 'register' && $method === 'POST') {
 [$ok, $result] = register_user(
  (string)($body['username'] ?? ''),
  (string)($body['display_name'] ?? ''),
  (string)($body['password'] ?? ''),
  (string)($body['confirm'] ?? ''),
  (string)($body['email'] ?? '')
 );
 if (!$ok) { json_out(['ok' => false, 'message' => $result], 400); }
 json_out(['ok' => true, 'redirect' => $result]); }

if ($action === 'login' && $method === 'POST') {
 [$ok, $result] = login_user((string)($body['username'] ?? ''), (string)($body['password'] ?? ''));
 if (!$ok) { json_out(['ok' => false, 'message' => $result], 400); }
 $next = safe_next((string)($body['next'] ?? ''));
 if ($next !== '' && $result === '/dashboard.php') { $result = $next; }
 json_out(['ok' => true, 'redirect' => $result]); }

if ($action === 'logout' && $method === 'POST') {
 api_user(false);
 end_session();
 json_out(['ok' => true, 'redirect' => '/']); }

if ($action === 'ingredients' && $method === 'GET') {
 api_user(false);
 $rows = [];
 foreach (catalog_all() as $row) {
  $rows[] = [
   'id' => (int)$row['id'],
   'inci_name' => (string)$row['inci_name'],
   'arabic_name' => (string)$row['arabic_name'],
   'synonyms' => (string)($row['synonyms'] ?? ''),
  ]; }
 json_out(['ok' => true, 'items' => $rows]); }

if ($action === 'allergens_save' && $method === 'POST') {
 $user = api_user(false);
 $names = $body['names'] ?? [];
 if (!is_array($names)) { $names = []; }
 if (count($names) > 40) { $names = array_slice($names, 0, 40); }
 [$added] = add_allergens((int)$user['id'], $names);
 if (!empty($body['finish'])) {
  finish_onboarding((int)$user['id']);
  json_out(['ok' => true, 'redirect' => '/dashboard.php']); }
 json_out(['ok' => true, 'message' => $added > 0 ? 'تمت إضافة المواد' : 'لم تُضف مواد جديدة']); }

if ($action === 'allergens_delete' && $method === 'POST') {
 $user = api_user(false);
 $id = (int)($body['id'] ?? 0);
 if ($id < 1 || !delete_allergen((int)$user['id'], $id)) { json_out(['ok' => false, 'message' => 'تعذر الحذف'], 400); }
 json_out(['ok' => true]); }

if ($action === 'onboarding_skip' && $method === 'POST') {
 $user = api_user(false);
 finish_onboarding((int)$user['id']);
 json_out(['ok' => true, 'redirect' => '/dashboard.php']); }

if ($action === 'lookup' && $method === 'POST') {
 $user = api_user(true);
 $code = clean_barcode((string)($body['barcode'] ?? ''));
 if ($code === '' || !in_array(strlen($code), [8, 12, 13], true)) { json_out(['ok' => false, 'message' => 'طول الباركود غير مدعوم. الصيغ المدعومة هي EAN-13 وEAN-8 وUPC-A وUPC-E'], 400); }
 ensure_product($code, (int)$user['id']);
 json_out(['ok' => true, 'redirect' => '/product.php?barcode=' . $code]); }

if ($action === 'product_save' && $method === 'POST') {
 $user = api_user(true);
 $id = (int)($body['id'] ?? 0);
 [$ok, $result] = save_user_product((int)$user['id'], $body, $id > 0 ? $id : null);
 if (!$ok) { json_out(['ok' => false, 'message' => $result], 400); }
 json_out(['ok' => true, 'redirect' => $result]); }

if ($action === 'product_delete' && $method === 'POST') {
 $user = api_user(true);
 $id = (int)($body['id'] ?? 0);
 if ($id < 1 || !delete_user_product((int)$user['id'], $id)) { json_out(['ok' => false, 'message' => 'تعذر الحذف'], 400); }
 json_out(['ok' => true]); }

if ($action === 'unread' && $method === 'GET') {
 $user = api_user(true);
 json_out(unread_payload((int)$user['id'])); }

if ($action === 'notification_read' && $method === 'POST') {
 $user = api_user(true);
 mark_notification((int)$user['id'], (int)($body['id'] ?? 0));
 json_out(['ok' => true, 'unread' => unread_count((int)$user['id'])]); }

if ($action === 'notifications_read_all' && $method === 'POST') {
 $user = api_user(true);
 mark_all_notifications((int)$user['id']);
 json_out(['ok' => true, 'unread' => 0]); }

if ($action === 'account' && $method === 'POST') {
 $user = api_user(true);
 [$ok, $message] = update_profile(
  (int)$user['id'],
  (string)($body['display_name'] ?? ''),
  (string)($body['email'] ?? ''),
  (int)($body['notice_days'] ?? 0),
  (string)($body['current_password'] ?? '')
 );
 if (!$ok) { json_out(['ok' => false, 'message' => $message], 400); }
 json_out(['ok' => true, 'message' => $message]); }

if ($action === 'admin_verify' && $method === 'POST') {
 $user = api_user(true);
 if ((int)($user['is_admin'] ?? 0) !== 1) { json_out(['ok' => false, 'message' => 'غير مسموح'], 403); }
 $id = (int)($body['id'] ?? 0);
 $flag = (int)($body['is_verified'] ?? 0) === 1 ? 1 : 0;
 if ($id < 1 || !db_one('SELECT id FROM products WHERE id = ?', [$id])) { json_out(['ok' => false, 'message' => 'تعذر الحفظ'], 400); }
 db_exec('UPDATE products SET is_verified = ? WHERE id = ?', [$flag, $id]);
 json_out(['ok' => true]); }

if ($action === 'password' && $method === 'POST') {
 $user = api_user(true);
 [$ok, $message] = change_password(
  (int)$user['id'],
  (string)($body['current_password'] ?? ''),
  (string)($body['password'] ?? ''),
  (string)($body['confirm'] ?? '')
 );
 if (!$ok) { json_out(['ok' => false, 'message' => $message], 400); }
 json_out(['ok' => true, 'message' => $message]); }

json_out(['ok' => false, 'message' => 'الطلب غير معروف'], 404);
