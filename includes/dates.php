<?php
declare(strict_types=1);

function now_utc(): DateTimeImmutable {
 return new DateTimeImmutable('now', new DateTimeZone('UTC')); }

function now_iso(): string {
 return now_utc()->format('Y-m-d\TH:i:s\Z'); }

function today_riyadh(): DateTimeImmutable {
 return now_utc()->setTimezone(new DateTimeZone('Asia/Riyadh'))->setTime(0, 0); }

function date_parts(string $isoDay): array {
 $dt = new DateTimeImmutable(substr($isoDay, 0, 10));
 return ['y' => (int)$dt->format('Y'), 'm' => (int)$dt->format('n'), 'd' => (int)$dt->format('j')]; }

function datetime_parts(string $iso): array {
 $dt = new DateTimeImmutable($iso);
 $local = $dt->setTimezone(new DateTimeZone('Asia/Riyadh'));
 $hour = (int)$local->format('G');
 $suffix = $hour < 12 ? 'ص' : 'م';
 $hour12 = $hour % 12;
 if ($hour12 === 0) { $hour12 = 12; }
 return [
  'y' => (int)$local->format('Y'),
  'm' => (int)$local->format('n'),
  'd' => (int)$local->format('j'),
  'h' => $hour12,
  'min' => $local->format('i'),
  'suffix' => $suffix,
 ]; }

function plain_date(string $isoDay): string {
 $parts = date_parts($isoDay);
 return $parts['y'] . '/' . $parts['m'] . '/' . $parts['d'] . 'م'; }

function plain_datetime(string $iso): string {
 $parts = datetime_parts($iso);
 return $parts['y'] . '/' . $parts['m'] . '/' . $parts['d'] . 'م ' . $parts['h'] . ':' . $parts['min'] . $parts['suffix']; }

function show_date(?array $parts): string {
 if (!$parts) { return ''; }
 return (int)$parts['y'] . '/' . (int)$parts['m'] . '/' . (int)$parts['d'] . 'م'; }

function show_datetime(?array $parts): string {
 if (!$parts) { return ''; }
 $suffix = ($parts['suffix'] ?? '') === 'ص' ? 'ص' : 'م';
 $min = preg_match('/^\d{2}$/', (string)($parts['min'] ?? '')) ? (string)$parts['min'] : '00';
 return (int)$parts['y'] . '/' . (int)$parts['m'] . '/' . (int)$parts['d'] . 'م ' . (int)$parts['h'] . ':' . $min . $suffix; }
