<?php
declare(strict_types=1);

function check_digit(string $data, int $leftOddWeight): int {
 $other = $leftOddWeight === 3 ? 1 : 3;
 $total = 0;
 $length = strlen($data);
 for ($i = 0; $i < $length; $i++) {
  $number = (int)$data[$i];
  $total += $number * ($i % 2 === 0 ? $leftOddWeight : $other); }
 return (10 - ($total % 10)) % 10; }

function make_ean13(string $twelve): string {
 return $twelve . (string)check_digit($twelve, 1); }

function make_ean8(string $seven): string {
 return $seven . (string)check_digit($seven, 1); }

function make_upca(string $eleven): string {
 return $eleven . (string)check_digit($eleven, 3); }

function valid_ean13(string $code): bool {
 return strlen($code) === 13 && ctype_digit($code) && (int)$code[12] === check_digit(substr($code, 0, 12), 1); }

function valid_ean8(string $code): bool {
 return strlen($code) === 8 && ctype_digit($code) && (int)$code[7] === check_digit(substr($code, 0, 7), 1); }

function valid_upca(string $code): bool {
 return strlen($code) === 12 && ctype_digit($code) && (int)$code[11] === check_digit(substr($code, 0, 11), 3); }

function expand_upce(string $code): string {
 if (strlen($code) !== 8 || !ctype_digit($code) || ($code[0] !== '0' && $code[0] !== '1')) { return ''; }
 $digits = substr($code, 1, 6);
 $last = $digits[5];
 if ($last === '0' || $last === '1' || $last === '2') {
  $maker = $digits[0] . $digits[1] . $last;
  $product = '0000' . $digits[2] . $digits[3] . $digits[4]; }
 elseif ($last === '3') {
  $maker = substr($digits, 0, 3);
  $product = '00000' . $digits[3] . $digits[4]; }
 elseif ($last === '4') {
  $maker = substr($digits, 0, 4);
  $product = '00000' . $digits[4]; }
 else {
  $maker = substr($digits, 0, 5);
  $product = '0000' . $last; }
 return $code[0] . $maker . $product . $code[7]; }

function valid_upce(string $code): bool {
 $expanded = expand_upce($code);
 return $expanded !== '' && valid_upca($expanded); }

function clean_barcode(string $raw): string {
 $raw = trim($raw);
 $digits = '';
 $length = strlen($raw);
 for ($i = 0; $i < $length; $i++) {
  $char = $raw[$i];
  if ($char === ' ' || $char === '-') { continue; }
  if (!ctype_digit($char)) { return ''; }
  $digits .= $char; }
 return $digits; }

function is_valid_barcode(string $code): bool {
 if ($code === '' || !ctype_digit($code)) { return false; }
 if (strlen($code) === 13) { return valid_ean13($code); }
 if (strlen($code) === 12) { return valid_upca($code); }
 if (strlen($code) === 8) { return valid_ean8($code) || valid_upce($code); }
 return false; }
