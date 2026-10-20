<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require_ready();
$user = current_user();
$rows = list_user_products((int)$user['id'], (int)$user['notice_days']);
$pageTitle = 'منتجاتي';
$pageId = 'products';
require __DIR__ . '/includes/header.php';
?>
<h1>منتجاتي</h1>
<p><a class="btn" href="/product_form.php"><i class="fa-solid fa-plus"></i> إضافة منتج</a></p>
<?php if ($rows === []) { ?>
<p>لا توجد منتجات محفوظة.</p>
<?php } else { ?>
<div class="table-wrap">
<table class="data-table">
<thead><tr><th>المنتج</th><th>الحالة</th><th>التاريخ</th><th>إجراءات</th></tr></thead>
<tbody>
<?php foreach ($rows as $row) {
 $photo = product_photo((string)($row['barcode'] ?? '')); ?>
<tr>
<td data-label="المنتج">
<span class="product-cell">
<?php if ($photo !== '') { ?><img src="<?php echo h($photo); ?>" alt=""><?php } ?>
<span>
<p class="primary-text"><?php echo h((string)$row['custom_name']); ?></p>
<p class="muted"><?php echo h((string)$row['reason_text']); ?></p>
</span>
</span>
</td>
<td class="st-<?php echo h((string)$row['status']); ?>" data-label="الحالة"><?php echo h((string)$row['status_text']); ?></td>
<td data-label="التاريخ"><?php echo h(show_date($row['parts'])); ?></td>
<td data-label="إجراءات">
<p class="row-actions">
<?php if (!empty($row['barcode'])) { ?><a href="/product.php?barcode=<?php echo h((string)$row['barcode']); ?>"><i class="fa-solid fa-file-lines"></i> عرض البيانات</a><?php } ?>
<a href="/product_form.php?id=<?php echo (int)$row['id']; ?>"><i class="fa-solid fa-pen"></i> تعديل</a>
<button type="button" class="btn-ghost" data-delete-product="<?php echo (int)$row['id']; ?>"><i class="fa-solid fa-trash"></i> حذف المنتج</button>
</p>
</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
<?php } ?>
<?php require __DIR__ . '/includes/footer.php';
