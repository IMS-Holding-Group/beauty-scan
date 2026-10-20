<?php
declare(strict_types=1);

function current_user(): ?array {
 return $GLOBALS['current_user'] ?? null; }

function current_csrf(): string {
 return (string)($GLOBALS['current_csrf'] ?? ''); }

function dummy_hash(): string {
 static $hash = null;
 if ($hash === null) { $hash = password_hash(bin2hex(random_bytes(8)), PASSWORD_BCRYPT); }
 return $hash; }

function load_session(): void {
 $GLOBALS['current_user'] = null;
 $GLOBALS['current_csrf'] = '';
 $raw = (string)($_COOKIE['bs_session'] ?? '');
 if ($raw !== '') {
  $row = db_one('SELECT s.id, s.csrf_token, s.last_seen_at, u.id AS user_id, u.username, u.display_name, u.email, u.notice_days, u.onboarding_done, u.is_admin, u.created_at FROM sessions s INNER JOIN users u ON u.id = s.user_id WHERE s.id = ?', [token_hash($raw)]);
  if ($row) {
   $seen = new DateTimeImmutable((string)$row['last_seen_at']);
   if ($seen < now_utc()->modify('-30 minutes')) { db_exec('DELETE FROM sessions WHERE id = ?', [$row['id']]); }
   else {
    db_exec('UPDATE sessions SET last_seen_at = ? WHERE id = ?', [now_iso(), $row['id']]);
    $GLOBALS['current_user'] = [
     'id' => (int)$row['user_id'],
     'username' => (string)$row['username'],
     'display_name' => (string)$row['display_name'],
     'email' => (string)($row['email'] ?? ''),
     'notice_days' => (int)$row['notice_days'],
     'onboarding_done' => (int)$row['onboarding_done'],
     'is_admin' => (int)($row['is_admin'] ?? 0),
     'created_at' => (string)$row['created_at'],
     'session_id' => (string)$row['id'],
    ];
    $GLOBALS['current_csrf'] = (string)$row['csrf_token'];
    return; } } }
 $cookie = (string)($_COOKIE['bs_csrf'] ?? '');
 if (str_contains($cookie, '.')) {
  [$token, $sig] = explode('.', $cookie, 2);
  if ($token !== '' && hash_equals(sign_token($token), $sig)) {
   $GLOBALS['current_csrf'] = $token;
   return; } }
 $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
 $GLOBALS['current_csrf'] = $token;
 $GLOBALS['issue_anon_csrf'] = $token; }

function issue_anon_cookie(): void {
 $token = (string)($GLOBALS['issue_anon_csrf'] ?? '');
 if ($token === '') { return; }
 cookie_set('bs_csrf', $token . '.' . sign_token($token), 60 * 60 * 8, false); }

function csrf_ok(): bool {
 $header = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
 $expected = current_csrf();
 return $header !== '' && $expected !== '' && hash_equals($expected, $header); }

function start_session(int $userId): void {
 $raw = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
 $csrf = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
 $now = now_iso();
 db_insert('INSERT INTO sessions (id, user_id, csrf_token, created_at, last_seen_at) VALUES (?, ?, ?, ?, ?)', [token_hash($raw), $userId, $csrf, $now, $now]);
 cookie_set('bs_session', $raw, 60 * 60 * 8, true); }

function end_session(): void {
 $user = current_user();
 if ($user) { db_exec('DELETE FROM sessions WHERE id = ?', [$user['session_id']]); }
 cookie_clear('bs_session'); }

function valid_username(string $username): bool {
 if (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $username)) { return false; }
 if (ctype_digit($username)) { return false; }
 return true; }

function valid_email(string $email): bool {
 if ($email === '') { return true; }
 return (bool)preg_match('/^[^@\s]+@[^@\s]+\.[^@\s]+$/', $email) && strlen($email) <= 120; }

function normalize_username(string $username): string {
 return strtolower(trim($username)); }

function login_locked(string $username, string $ip): bool {
 $since = now_utc()->modify('-15 minutes')->format('Y-m-d\TH:i:s\Z');
 $row = db_one('SELECT COUNT(*) AS c FROM login_attempts WHERE username = ? AND ip = ? AND attempted_at > ?', [$username, $ip, $since]);
 return (int)($row['c'] ?? 0) >= 5; }

function register_user(string $username, string $display, string $password, string $confirm, string $email): array {
 $username = normalize_username($username);
 $display = trim($display);
 $email = trim($email);
 if (!valid_username($username)) { return [false, 'اسم المستخدم من 3 إلى 30 حرفاً لاتينياً، ويقبل الأرقام والنقطة والشرطة السفلية']; }
 if (ctype_digit($username)) { return [false, 'اسم المستخدم غير مقبول']; }
 if (mb_strlen($display, 'UTF-8') < 2 || mb_strlen($display, 'UTF-8') > 60) { return [false, 'اكتب الاسم المعروض']; }
 if (strlen($password) < 8 || $password !== $confirm) { return [false, 'كلمة السر 8 أحرف على الأقل ويجب أن تتطابق مع التأكيد']; }
 if (!valid_email($email)) { return [false, 'البريد غير صالح']; }
 $exists = db_one('SELECT id FROM users WHERE username = ?', [$username]);
 if ($exists) { return [false, 'اسم المستخدم مستخدم']; }
 $id = (int)db_insert(
  'INSERT INTO users (username, display_name, email, password_hash, notice_days, onboarding_done, created_at) VALUES (?, ?, ?, ?, 30, 0, ?)',
  [$username, $display, $email === '' ? null : $email, password_hash($password, PASSWORD_BCRYPT), now_iso()]
 );
 start_session($id);
 return [true, '/onboarding.php']; }

function login_user(string $username, string $password): array {
 $username = normalize_username($username);
 $ip = client_ip();
 db_exec('DELETE FROM login_attempts WHERE attempted_at < ?', [now_utc()->modify('-1 day')->format('Y-m-d\TH:i:s\Z')]);
 if ($username === '' || $password === '') { return [false, 'بيانات الدخول غير صحيحة']; }
 if (login_locked($username, $ip)) { return [false, 'تعذر تسجيل الدخول حالياً. حاول لاحقاً']; }
 $user = db_one('SELECT id, password_hash, onboarding_done FROM users WHERE username = ?', [$username]);
 $hash = $user['password_hash'] ?? dummy_hash();
 $ok = password_verify($password, (string)$hash);
 if (!$user || !$ok) {
  db_insert('INSERT INTO login_attempts (username, ip, attempted_at) VALUES (?, ?, ?)', [$username, $ip, now_iso()]);
  return [false, 'بيانات الدخول غير صحيحة']; }
 start_session((int)$user['id']);
 scan_user((int)$user['id']);
 if ((int)$user['onboarding_done'] !== 1) { return [true, '/onboarding.php']; }
 return [true, '/dashboard.php']; }

function update_profile(int $userId, string $display, string $email, int $noticeDays, string $currentPassword): array {
 $display = trim($display);
 $email = trim($email);
 if (mb_strlen($display, 'UTF-8') < 2 || mb_strlen($display, 'UTF-8') > 60) { return [false, 'اكتب الاسم المعروض']; }
 if (!valid_email($email)) { return [false, 'البريد غير صالح']; }
 if ($noticeDays < 1 || $noticeDays > 180) { return [false, 'عدد أيام التنبيه بين 1 و 180']; }
 $user = db_one('SELECT email, password_hash FROM users WHERE id = ?', [$userId]);
 if (!$user) { return [false, 'تعذر حفظ البيانات']; }
 $oldEmail = (string)($user['email'] ?? '');
 if ($email !== $oldEmail && !password_verify($currentPassword, (string)$user['password_hash'])) { return [false, 'اكتب كلمة السر الحالية لتغيير البريد']; }
 db_exec('UPDATE users SET display_name = ?, email = ?, notice_days = ? WHERE id = ?', [$display, $email === '' ? null : $email, $noticeDays, $userId]);
 return [true, 'تم حفظ البيانات']; }

function change_password(int $userId, string $current, string $next, string $confirm): array {
 if (strlen($next) < 8 || $next !== $confirm) { return [false, 'كلمة السر الجديدة 8 أحرف على الأقل ويجب أن تتطابق مع التأكيد']; }
 $user = db_one('SELECT password_hash FROM users WHERE id = ?', [$userId]);
 if (!$user || !password_verify($current, (string)$user['password_hash'])) { return [false, 'كلمة السر الحالية غير صحيحة']; }
 db_exec('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($next, PASSWORD_BCRYPT), $userId]);
 return [true, 'تم تغيير كلمة السر']; }
