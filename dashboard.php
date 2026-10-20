<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require_ready();
$user = current_user();
scan_user((int)$user['id']);
$rows = list_user_products((int)$user['id'], (int)$user['notice_days']);
$counts = status_counts($rows);
$pageTitle = 'لوحة التحكم';
$pageId = 'dashboard';
require __DIR__ . '/includes/header.php';
?>
<h1>لوحة التحكم</h1>
<p>مرحباً <?php echo h($user['display_name']); ?></p>
<section class="stats" aria-label="ملخص الصلاحية">
<p><span class="stat"><?php echo $counts['ok']; ?></span> <span class="st-ok">صالح</span></p>
<p><span class="stat"><?php echo $counts['soon']; ?></span> <span class="st-soon">قريب الانتهاء</span></p>
<p><span class="stat"><?php echo $counts['week']; ?></span> <span class="st-week">ينتهي خلال 7 أيام</span></p>
<p><span class="stat"><?php echo $counts['expired']; ?></span> <span class="st-expired">منتهي</span></p>
</section>
<p><a class="btn" href="/scan.php">مسح منتج</a></p>
<?php if ($rows === []) { ?>
<p>لا توجد منتجات في حسابك بعد.</p>
<?php } else { ?>
<div class="table-wrap">
<table class="data-table">
<thead><tr><th>المنتج</th><th>الحالة</th><th>التاريخ الفعلي</th></tr></thead>
<tbody>
<?php foreach (array_slice($rows, 0, 8) as $row) { ?>
<tr>
<td data-label="المنتج"><?php echo h((string)$row['custom_name']); ?></td>
<td class="st-<?php echo h((string)$row['status']); ?>" data-label="الحالة"><?php echo h((string)$row['status_text']); ?></td>
<td data-label="التاريخ الفعلي"><?php echo show_date($row['parts']); ?></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
<?php } ?>
<?php require __DIR__ . '/includes/footer.php';
