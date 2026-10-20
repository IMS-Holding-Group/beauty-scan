<?php
declare(strict_types=1);

require __DIR__ . '/env.php';
load_env(app_root() . '/.env');

$dataDir = app_root() . '/data';
if (!is_dir($dataDir)) { mkdir($dataDir, 0700, true); }
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', $dataDir . '/app.log');
error_reporting(E_ALL);

require __DIR__ . '/security.php';
require __DIR__ . '/dates.php';
require __DIR__ . '/text.php';
require __DIR__ . '/barcode.php';
require __DIR__ . '/expiry.php';
require __DIR__ . '/match.php';
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/mailer.php';
require __DIR__ . '/notify.php';
require __DIR__ . '/products.php';
require __DIR__ . '/cabinet.php';
require __DIR__ . '/allergens.php';
require __DIR__ . '/seed.php';

set_exception_handler(function (Throwable $error): void {
 error_log('request failed');
 if (PHP_SAPI === 'cli') { return; }
 $message = $error instanceof PDOException ? 'تعذر الاتصال بقاعدة البيانات' : 'حدث خطأ غير متوقع';
 $script = (string)($_SERVER['SCRIPT_NAME'] ?? '');
 if (str_ends_with($script, 'api.php')) {
  if (!headers_sent()) {
   http_response_code(500);
   header('Content-Type: application/json; charset=utf-8'); }
  echo json_encode(['ok' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
  return; }
 if (!headers_sent()) {
  http_response_code(500);
  header('Content-Type: text/html; charset=utf-8'); }
 echo '<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><title>خطأ</title><link rel="icon" href="/assets/images/favicon.svg"></head><body><p>' . $message . '</p></body></html>'; });

if (PHP_SAPI !== 'cli') {
 send_security_headers();
 init_db();
 load_session();
 issue_anon_cookie();
 maybe_background_scan(); }

function require_login(): void {
 if (!current_user()) {
  $next = safe_next((string)($_SERVER['REQUEST_URI'] ?? ''));
  $url = '/login.php';
  if ($next !== '') { $url .= '?next=' . rawurlencode($next); }
  header('Location: ' . $url);
  exit; } }

function require_ready(): void {
 require_login();
 $user = current_user();
 if ($user && (int)$user['onboarding_done'] !== 1) {
  header('Location: /onboarding.php');
  exit; } }

function require_admin(): void {
 require_ready();
 $user = current_user();
 if (!$user || (int)($user['is_admin'] ?? 0) !== 1) {
  http_response_code(403);
  $pageTitle = 'غير مسموح';
  $pageId = 'denied';
  require __DIR__ . '/header.php';
  echo '<h1>غير مسموح</h1><p>هذه الصفحة للمسؤول فقط.</p>';
  require __DIR__ . '/footer.php';
  exit; } }
