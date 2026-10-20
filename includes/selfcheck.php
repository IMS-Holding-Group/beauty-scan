<?php
declare(strict_types=1);

require __DIR__ . '/barcode.php';
require __DIR__ . '/text.php';
require __DIR__ . '/dates.php';
require __DIR__ . '/expiry.php';
require __DIR__ . '/match.php';

$sample13 = make_ean13('628100000001');
if (!valid_ean13($sample13)) { fwrite(STDERR, "ean13 fail\n"); exit(1); }
if (valid_ean13(substr($sample13, 0, 12) . (string)((int)$sample13[12] + 1) % 10)) { fwrite(STDERR, "ean13 false positive\n"); exit(1); }
if (make_ean8('9638507') !== '96385074' || !valid_ean8('96385074')) { fwrite(STDERR, "ean8 fail\n"); exit(1); }
$sample12 = make_upca('12345678901');
if (!valid_upca($sample12)) { fwrite(STDERR, "upca fail\n"); exit(1); }
$upce = '0' . '123451' . (string)check_digit('01210000345', 3);
if (!valid_upce($upce) || expand_upce($upce) !== '01210000345' . $upce[7]) { fwrite(STDERR, "upce fail\n"); exit(1); }
if (clean_barcode('12A') !== '') { fwrite(STDERR, "clean fail\n"); exit(1); }
if (normalize_text('  Parfum (Fragrance) ') !== 'parfum fragrance') { fwrite(STDERR, "norm fail\n"); exit(1); }
if (normalize_text('حمض،   الهيالورونيك') !== 'حمض الهيالورونيك') { fwrite(STDERR, "arabic norm fail\n"); exit(1); }
if (original_slice('Parfum (Fragrance)', 'parfum') !== 'Parfum') { fwrite(STDERR, "slice fail\n"); exit(1); }
if (plain_date('2026-01-03') !== '2026/1/3م') { fwrite(STDERR, "date fail\n"); exit(1); }
if (show_date(['y' => 2026, 'm' => 1, 'd' => 3]) !== '2026/1/3م') { fwrite(STDERR, "show date fail\n"); exit(1); }
if (show_datetime(['y' => 2026, 'm' => 1, 'd' => 3, 'h' => 5, 'min' => '50', 'suffix' => 'ص']) !== '2026/1/3م 5:50ص') { fwrite(STDERR, "show datetime fail\n"); exit(1); }
$morning = datetime_parts('2026-01-03T02:50:00+00:00');
if ($morning['h'] !== 5 || $morning['min'] !== '50' || $morning['suffix'] !== 'ص') { fwrite(STDERR, "morning fail\n"); exit(1); }
if (add_months(new DateTimeImmutable('2026-01-31'), 1)->format('Y-m-d') !== '2026-02-28') { fwrite(STDERR, "month fail\n"); exit(1); }
[$effective, $reason] = effective_expiry('2027-01-01', '2026-01-01', 6);
if ($effective !== '2026-07-01' || $reason !== 'pao') { fwrite(STDERR, "pao fail\n"); exit(1); }
[$status] = status_for('2026-10-24', 30, new DateTimeImmutable('2026-10-19'));
if ($status !== 'week') { fwrite(STDERR, "status fail\n"); exit(1); }
if (stages_reached(5, 30) !== ['soon', 'week']) { fwrite(STDERR, "stages fail\n"); exit(1); }
$catalog = [['id' => 1, 'inci_name' => 'Parfum', 'arabic_name' => 'عطر', 'function_ar' => 'رائحة', 'notes_ar' => '', 'synonyms' => 'fragrance']];
$hit = match_allergens('Aqua, Parfum (Fragrance)', [['normalized_name' => 'parfum', 'ingredient_id' => 1]], $catalog);
if ($hit['status'] !== 'hit' || !in_array('Parfum', $hit['names'], true)) { fwrite(STDERR, "match fail\n"); exit(1); }
$missing = match_allergens('   ', [], $catalog);
if ($missing['status'] !== 'missing') { fwrite(STDERR, "missing fail\n"); exit(1); }
echo "selfcheck ok\n";
echo $sample13 . "\n";
