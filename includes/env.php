<?php
declare(strict_types=1);

function app_root(): string {
 return dirname(__DIR__); }

function load_env(string $path): void {
 if (!is_file($path)) { return; }
 $lines = file($path, FILE_IGNORE_NEW_LINES);
 if ($lines === false) { return; }
 foreach ($lines as $line) {
  $line = trim($line);
  if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) { continue; }
  [$key, $value] = explode('=', $line, 2);
  $key = trim($key);
  $value = trim($value);
  $len = strlen($value);
  if ($len >= 2 && $value[0] === $value[$len - 1] && ($value[0] === '"' || $value[0] === "'")) {
   $value = substr($value, 1, -1); }
  if ($key !== '' && getenv($key) === false) {
   putenv($key . '=' . $value);
   $_ENV[$key] = $value; } } }

function env(string $key, string $default = ''): string {
 $value = getenv($key);
 if ($value === false) { return $default; }
 return trim((string)$value); }
