<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli-server') {
 http_response_code(404);
 header('Content-Type: text/plain; charset=utf-8');
 echo 'غير موجود';
 return; }

$uri = rawurldecode((string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?? '/'));
$lower = strtolower($uri);
$blocked = $uri !== $lower && str_contains($uri, '..');
if (str_contains($uri, '..') || str_contains($uri, '\\')) { $blocked = true; }
$exact = ['/.env', '/.env.example', '/.gitignore', '/.htaccess', '/readme.md', '/router.php', '/test_accounts.txt'];
if (in_array($lower, $exact, true)) { $blocked = true; }
foreach (['/includes/', '/data/', '/modules/', '/.git/'] as $prefix) {
 if (str_starts_with($lower, $prefix)) { $blocked = true; } }
if (preg_match('/\.(?:sqlite|sql|log|bak|backup|ini)$/', $lower)) { $blocked = true; }
if ($blocked) {
 http_response_code(404);
 header('Content-Type: text/plain; charset=utf-8');
 header('X-Content-Type-Options: nosniff');
 echo 'غير موجود';
 return true; }
$path = __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, $uri);
if ($uri === '/' || is_file($path)) { return false; }
http_response_code(404);
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><title>غير موجود</title><link rel="icon" href="/assets/images/favicon.svg"></head><body><p>الصفحة غير موجودة</p></body></html>';
return true;
