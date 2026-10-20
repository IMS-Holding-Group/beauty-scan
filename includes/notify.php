<?php
declare(strict_types=1);

function notification_lead(string $type, string $name): string {
 if ($type === 'week') { return $name . ' ينتهي خلال 7 أيام.'; }
 if ($type === 'expired') { return $name . ' منتهي الصلاحية.'; }
 return 'اقترب موعد انتهاء ' . $name . '.'; }

function scan_user(int $userId): void {
 $user = db_one('SELECT id, email, notice_days FROM users WHERE id = ?', [$userId]);
 if (!$user) { return; }
 $rows = db_all('SELECT * FROM user_products WHERE user_id = ?', [$userId]);
 $today = today_riyadh();
 $notice = (int)$user['notice_days'];
 foreach ($rows as $row) {
  $pao = $row['pao_months'] === null || $row['pao_months'] === '' ? null : (int)$row['pao_months'];
  [$effective, $reason] = effective_expiry((string)$row['expiry_date'], $row['opened_date'] ?: null, $pao);
  [, $days] = status_for($effective, $notice, $today);
  $name = trim((string)$row['custom_name']) !== '' ? (string)$row['custom_name'] : 'منتج';
  foreach (stages_reached($days, $notice) as $type) {
   $lead = notification_lead($type, $name);
   $message = $lead . ' التاريخ الفعلي ' . plain_date($effective) . '. ' . reason_text($reason) . '.';
   try {
    db_insert('INSERT INTO notifications (user_id, user_product_id, type, message, dedupe_key, is_read, created_at) VALUES (?, ?, ?, ?, ?, 0, ?)', [
     $userId, (int)$row['id'], $type, mb_substr($message, 0, 500), (int)$row['id'] . ':' . $type, now_iso(),
    ]);
    $email = trim((string)($user['email'] ?? ''));
    if ($email !== '') { send_mail($email, 'تنبيه صلاحية', $message); } }
   catch (PDOException $error) {
    if ((string)$error->getCode() !== '23000') { throw $error; } } } } }

function scan_all(): void {
 foreach (db_all('SELECT id FROM users') as $row) { scan_user((int)$row['id']); } }

function maybe_background_scan(): void {
 // ponytail: PHP لا يبقي خيطاً بين الطلبات، والفحص العام يعمل إذا مضت 6 ساعات على آخر فحص في data/last_scan.txt
 $path = app_root() . '/data/last_scan.txt';
 $dir = dirname($path);
 if (!is_dir($dir)) { mkdir($dir, 0700, true); }
 $handle = fopen($path, 'c+');
 if (!$handle) { return; }
 if (!flock($handle, LOCK_EX | LOCK_NB)) { fclose($handle); return; }
 $raw = stream_get_contents($handle);
 $last = is_string($raw) ? (int)trim($raw) : 0;
 if ($last === 0 || (time() - $last) >= 6 * 60 * 60) {
  scan_all();
  rewind($handle);
  ftruncate($handle, 0);
  fwrite($handle, (string)time()); }
 flock($handle, LOCK_UN);
 fclose($handle); }

function unread_count(int $userId): int {
 $row = db_one('SELECT COUNT(*) AS c FROM notifications WHERE user_id = ? AND is_read = 0', [$userId]);
 return (int)($row['c'] ?? 0); }

function list_notifications(int $userId): array {
 return db_all('SELECT n.id, n.type, n.message, n.is_read, n.created_at, p.custom_name, p.expiry_date, p.opened_date, p.pao_months FROM notifications n LEFT JOIN user_products p ON p.id = n.user_product_id AND p.user_id = n.user_id WHERE n.user_id = ? ORDER BY n.id DESC', [$userId]); }

function unread_payload(int $userId): array {
 $rows = db_all('SELECT n.id, n.type, n.created_at, p.custom_name, p.expiry_date, p.opened_date, p.pao_months FROM notifications n LEFT JOIN user_products p ON p.id = n.user_product_id AND p.user_id = n.user_id WHERE n.user_id = ? AND n.is_read = 0 ORDER BY n.id DESC LIMIT 20', [$userId]);
 $items = [];
 foreach ($rows as $row) {
  $name = trim((string)($row['custom_name'] ?? '')) !== '' ? (string)$row['custom_name'] : 'منتج';
  $parts = null;
  if (!empty($row['expiry_date'])) {
   $pao = $row['pao_months'] === null || $row['pao_months'] === '' ? null : (int)$row['pao_months'];
   [$effective] = effective_expiry((string)$row['expiry_date'], $row['opened_date'] ?: null, $pao);
   $parts = date_parts($effective); }
  $items[] = [
   'id' => (int)$row['id'],
   'lead' => notification_lead((string)$row['type'], $name),
   'parts' => $parts,
   'created' => datetime_parts((string)$row['created_at']),
  ]; }
 return ['ok' => true, 'unread' => unread_count($userId), 'items' => $items]; }

function mark_notification(int $userId, int $id): void {
 db_exec('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?', [$id, $userId]); }

function mark_all_notifications(int $userId): void {
 db_exec('UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0', [$userId]); }
