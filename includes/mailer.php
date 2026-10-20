<?php
declare(strict_types=1);

function smtp_read($socket): string {
 $data = '';
 while (!feof($socket)) {
  $line = fgets($socket, 515);
  if ($line === false) { break; }
  $data .= $line;
  if (isset($line[3]) && $line[3] === ' ') { break; } }
 return $data; }

function smtp_expect($socket, string $code): void {
 $data = smtp_read($socket);
 if (!str_starts_with($data, $code)) { throw new RuntimeException('smtp'); } }

function smtp_cmd($socket, string $command, string $code): void {
 fwrite($socket, $command . "\r\n");
 smtp_expect($socket, $code); }

function send_mail(string $to, string $subject, string $body): void {
 $host = env('SMTP_HOST');
 $port = (int)env('SMTP_PORT', '587');
 $user = env('SMTP_USER');
 $pass = env('SMTP_PASSWORD');
 $from = env('SMTP_FROM');
 if ($host === '' || $port <= 0 || $user === '' || $pass === '' || $from === '' || $to === '') { return; }
 if (!valid_email($to) || !valid_email($from) || $to === '' || $from === '') { return; }
 $socket = null;
 try {
  $socket = @stream_socket_client('tcp://' . $host . ':' . $port, $errno, $errstr, 10);
  if (!$socket) { return; }
  stream_set_timeout($socket, 10);
  smtp_expect($socket, '220');
  smtp_cmd($socket, 'EHLO localhost', '250');
  smtp_cmd($socket, 'STARTTLS', '220');
  if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { throw new RuntimeException('tls'); }
  smtp_cmd($socket, 'EHLO localhost', '250');
  smtp_cmd($socket, 'AUTH LOGIN', '334');
  smtp_cmd($socket, base64_encode($user), '334');
  smtp_cmd($socket, base64_encode($pass), '235');
  smtp_cmd($socket, 'MAIL FROM:<' . $from . '>', '250');
  smtp_cmd($socket, 'RCPT TO:<' . $to . '>', '250');
  smtp_cmd($socket, 'DATA', '354');
  $safeBody = str_replace(["\r\n", "\r"], "\n", $body);
  $lines = explode("\n", $safeBody);
  foreach ($lines as $i => $line) {
   if (str_starts_with($line, '.')) { $lines[$i] = '.' . $line; } }
  $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
  $message = 'From: ' . $from . "\r\nTo: " . $to . "\r\nSubject: " . $encodedSubject . "\r\nMIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\n" . implode("\r\n", $lines) . "\r\n.\r\n";
  fwrite($socket, $message);
  smtp_expect($socket, '250');
  fwrite($socket, "QUIT\r\n"); }
 catch (Throwable $error) { error_log('mail failed'); }
 finally { if (is_resource($socket)) { fclose($socket); } } }
