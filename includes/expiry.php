<?php
declare(strict_types=1);

function add_months(DateTimeImmutable $start, int $months): DateTimeImmutable {
 $monthIndex = ((int)$start->format('n') - 1) + $months;
 $year = (int)$start->format('Y') + intdiv($monthIndex, 12);
 $month = $monthIndex % 12;
 if ($month < 0) {
  $month += 12;
  $year -= 1; }
 $month += 1;
 $day = (int)$start->format('j');
 $last = (int)(new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month)))->modify('last day of this month')->format('j');
 $use = min($day, $last);
 return new DateTimeImmutable(sprintf('%04d-%02d-%02d', $year, $month, $use)); }

function parse_iso_day(?string $value): ?DateTimeImmutable {
 if ($value === null || $value === '') { return null; }
 $day = substr($value, 0, 10);
 if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)) { return null; }
 $dt = DateTimeImmutable::createFromFormat('!Y-m-d', $day);
 $errors = DateTimeImmutable::getLastErrors();
 if (!$dt || ($errors && (($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0))) { return null; }
 return $dt; }

function effective_expiry(string $expiryIso, ?string $openedIso, ?int $paoMonths): array {
 $expiry = parse_iso_day($expiryIso);
 if (!$expiry) { throw new InvalidArgumentException('expiry'); }
 $opened = parse_iso_day($openedIso);
 $reason = 'expiry';
 $effective = $expiry;
 if ($opened && $paoMonths) {
  $paoDate = add_months($opened, $paoMonths);
  if ($paoDate < $expiry) {
   $effective = $paoDate;
   $reason = 'pao'; } }
 return [$effective->format('Y-m-d'), $reason]; }

function status_for(string $effectiveIso, int $noticeDays, DateTimeImmutable $today): array {
 $effective = parse_iso_day($effectiveIso);
 if (!$effective) { return ['expired', 0]; }
 $from = new DateTimeImmutable($today->format('Y-m-d'));
 $to = new DateTimeImmutable($effective->format('Y-m-d'));
 $days = (int)$from->diff($to)->format('%r%a');
 if ($days < 0) { return ['expired', $days]; }
 if ($days <= 7) { return ['week', $days]; }
 if ($days <= $noticeDays) { return ['soon', $days]; }
 return ['ok', $days]; }

function stages_reached(int $days, int $noticeDays): array {
 $stages = [];
 if ($days <= $noticeDays) { $stages[] = 'soon'; }
 if ($days <= 7) { $stages[] = 'week'; }
 if ($days < 0) { $stages[] = 'expired'; }
 return $stages; }

function reason_text(string $reason): string {
 if ($reason === 'pao') { return 'انتهاء مدة الاستخدام بعد الفتح'; }
 return 'انتهاء الصلاحية المطبوع'; }

function status_label(string $status): string {
 $labels = [
  'ok' => 'صالح',
  'soon' => 'قريب الانتهاء',
  'week' => 'ينتهي خلال 7 أيام',
  'expired' => 'منتهي',
 ];
 return $labels[$status] ?? 'صالح'; }
