<?php
declare(strict_types=1);

function char_code(string $char): int {
 if (function_exists('mb_ord')) { return mb_ord($char, 'UTF-8'); }
 $packed = iconv('UTF-8', 'UCS-4BE', $char);
 if ($packed === false) { return 0; }
 $unpacked = unpack('N', $packed);
 return (int)($unpacked[1] ?? 0); }

function is_token_char(string $char): bool {
 $code = char_code($char);
 if ($code >= 48 && $code <= 57) { return true; }
 if ($code >= 97 && $code <= 122) { return true; }
 if ($code >= 0x0621 && $code <= 0x064A) { return true; }
 return false; }

function normalize_text(string $value): string {
 $value = mb_strtolower($value, 'UTF-8');
 $chars = [];
 $previousSpace = false;
 $length = mb_strlen($value, 'UTF-8');
 for ($i = 0; $i < $length; $i++) {
  $char = mb_substr($value, $i, 1, 'UTF-8');
  if (is_token_char($char)) {
   $chars[] = $char;
   $previousSpace = false; }
  elseif (preg_match('/\s/u', $char)) {
   if ($chars !== [] && !$previousSpace) {
    $chars[] = ' ';
    $previousSpace = true; } } }
 return trim(implode('', $chars)); }

function normalize_with_map(string $value): array {
 $lower = mb_strtolower($value, 'UTF-8');
 $chars = [];
 $mapping = [];
 $previousSpace = false;
 $length = mb_strlen($lower, 'UTF-8');
 for ($i = 0; $i < $length; $i++) {
  $char = mb_substr($lower, $i, 1, 'UTF-8');
  if (is_token_char($char)) {
   $chars[] = $char;
   $mapping[] = $i;
   $previousSpace = false; }
  elseif (preg_match('/\s/u', $char)) {
   if ($chars !== [] && !$previousSpace) {
    $chars[] = ' ';
    $mapping[] = $i;
    $previousSpace = true; } } }
 while ($chars !== [] && $chars[0] === ' ') {
  array_shift($chars);
  array_shift($mapping); }
 while ($chars !== [] && $chars[count($chars) - 1] === ' ') {
  array_pop($chars);
  array_pop($mapping); }
 return [implode('', $chars), $mapping]; }

function original_slice(string $segment, string $needle): string {
 [$norm, $mapping] = normalize_with_map($segment);
 $index = mb_strpos($norm, $needle, 0, 'UTF-8');
 if ($index === false || $mapping === []) { return trim($segment); }
 $endIndex = $index + mb_strlen($needle, 'UTF-8') - 1;
 if (!isset($mapping[$endIndex])) { return trim($segment); }
 $start = $mapping[$index];
 $end = $mapping[$endIndex] + 1;
 return trim(mb_substr($segment, $start, $end - $start, 'UTF-8')); }

function split_parts(string $text): array {
 $parts = preg_split('/[,،;\n]+/u', $text) ?: [];
 $out = [];
 foreach ($parts as $part) {
  $part = trim((string)$part);
  if ($part !== '') { $out[] = $part; } }
 return $out; }
