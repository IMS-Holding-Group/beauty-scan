<?php
declare(strict_types=1);

function h(?string $value): string {
 return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function client_ip(): string {
 $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
 if (!filter_var($ip, FILTER_VALIDATE_IP)) { return '0.0.0.0'; }
 return $ip; }

function request_is_secure(): bool {
 $https = (string)($_SERVER['HTTPS'] ?? '');
 return $https !== '' && $https !== 'off'; }

function cookie_set(string $name, string $value, int $maxAge, bool $httpOnly): void {
 setcookie($name, $value, [
  'expires' => time() + $maxAge,
  'path' => '/',
  'secure' => request_is_secure(),
  'httponly' => $httpOnly,
  'samesite' => 'Lax',
 ]); }

function cookie_clear(string $name): void {
 setcookie($name, '', [
  'expires' => time() - 3600,
  'path' => '/',
  'secure' => request_is_secure(),
  'httponly' => true,
  'samesite' => 'Lax',
 ]); }

function token_hash(string $token): string {
 return hash('sha256', $token); }

function app_secret(): string {
 $secret = env('SECRET_KEY');
 if ($secret === '') { throw new RuntimeException('secret missing'); }
 return $secret; }

function sign_token(string $token): string {
 return hash_hmac('sha256', $token, app_secret()); }

function safe_next(string $value): string {
 if ($value === '' || $value[0] !== '/' || str_starts_with($value, '//') || str_contains($value, '\\') || str_contains($value, '://')) { return ''; }
 if (preg_match('/[\r\n]/', $value)) { return ''; }
 if (!preg_match('#^/[A-Za-z0-9_./?=&%-]*$#', $value)) { return ''; }
 return $value; }

function send_security_headers(): void {
 header('X-Frame-Options: DENY');
 header('X-Content-Type-Options: nosniff');
 header('Referrer-Policy: same-origin');
 header('Content-Security-Policy: default-src \'self\'; script-src \'self\'; style-src \'self\'; font-src \'self\'; img-src \'self\'; connect-src \'self\'; base-uri \'self\'; form-action \'self\'; frame-ancestors \'none\'');
 header('Permissions-Policy: camera=(self), microphone=(), geolocation=()');
 header_remove('X-Powered-By');
 if (request_is_secure()) { header('Strict-Transport-Security: max-age=31536000; includeSubDomains'); } }

function json_body(): array {
 $raw = file_get_contents('php://input');
 if ($raw === false || $raw === '') { return []; }
 if (strlen($raw) > 65536) { throw new RuntimeException('payload'); }
 $data = json_decode($raw, true);
 return is_array($data) ? $data : []; }

function json_out(array $payload, int $status = 200): void {
 http_response_code($status);
 header('Content-Type: application/json; charset=utf-8');
 echo json_encode($payload, JSON_UNESCAPED_UNICODE);
 exit; }
