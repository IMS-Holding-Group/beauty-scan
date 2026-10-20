<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require_admin();
$users = db_all('SELECT username, display_name, is_admin, created_at FROM users ORDER BY id ASC');
$products = db_all('SELECT id, barcode, name, brand, is_verified FROM products ORDER BY name ASC');
$notes = db_one('SELECT COUNT(*) AS c FROM notifications');
$pageTitle = 'الإدارة';
$pageId = 'admin';
require __DIR__ . '/includes/header.php';
?>
<h1>الإدارة</h1>
<section class="stats" aria-label="ملخص الإدارة">
<p><span class="stat"><?php echo count($users); ?></span> <span>الحسابات</span></p>
<p><span class="stat"><?php echo count($products); ?></span> <span>المنتجات</span></p>
<p><span class="stat"><?php echo (int)($notes['c'] ?? 0); ?></span> <span>التنبيهات</span></p>
</section>
<h2>الحسابات</h2>
<div class="table-wrap">
<table>
<thead><tr><th>اسم المستخدم</th><th>الاسم</th><th>النوع</th><th>تاريخ التسجيل</th></tr></thead>
<tbody>
<?php foreach ($users as $row) { ?>
<tr>
<td data-label="اسم المستخدم"><?php echo h((string)$row['username']); ?></td>
<td data-label="الاسم"><?php echo h((string)$row['display_name']); ?></td>
<td data-label="النوع"><?php echo (int)$row['is_admin'] === 1 ? 'مسؤول' : 'مستخدم'; ?></td>
<td data-label="تاريخ التسجيل"><?php echo show_datetime(datetime_parts((string)$row['created_at'])); ?></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
<h2>المنتجات</h2>
<div class="table-wrap">
<table>
<thead><tr><th>المنتج</th><th>الباركود</th><th>التوثيق</th><th></th></tr></thead>
<tbody>
<?php foreach ($products as $row) {
 $verified = (int)$row['is_verified'] === 1; ?>
<tr>
<td data-label="المنتج"><?php echo h((string)($row['name'] ?? '')); ?></td>
<td data-label="الباركود"><?php echo h((string)$row['barcode']); ?></td>
<td data-label="التوثيق"><?php echo $verified ? 'موثّق' : 'غير موثّق'; ?></td>
<td data-label="إجراء"><button type="button" data-verify="<?php echo (int)$row['id']; ?>" data-flag="<?php echo $verified ? '0' : '1'; ?>"><?php echo $verified ? 'إلغاء التوثيق' : 'توثيق'; ?></button></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
<p id="admin-message" class="msg" role="alert"></p>
<?php require __DIR__ . '/includes/footer.php';
