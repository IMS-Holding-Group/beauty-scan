<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require_ready();
$user = current_user();
$items = list_allergens((int)$user['id']);
$pageTitle = 'حساسيتي';
$pageId = 'allergens';
$finish = 0;
$showSkip = false;
require __DIR__ . '/includes/header.php';
?>
<h1>حساسيتي</h1>
<div class="layout">
<section>
<h2>المواد المسجلة</h2>
<?php if ($items === []) { ?>
<p>لا توجد مواد مسجلة.</p>
<?php } else { ?>
<div class="table-wrap">
<table class="data-table">
<thead><tr><th>المادة</th><th>إجراء</th></tr></thead>
<tbody>
<?php foreach ($items as $item) { ?>
<tr>
<td data-label="المادة"><?php echo h((string)$item['name']); ?></td>
<td data-label="إجراء"><button type="button" data-delete-allergen="<?php echo (int)$item['id']; ?>">حذف</button></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
<?php } ?>
</section>
<section>
<h2>إضافة مواد</h2>
<?php require __DIR__ . '/includes/allergen_form.php'; ?>
</section>
</div>
<?php require __DIR__ . '/includes/footer.php';
