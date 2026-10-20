<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require_ready();
$user = current_user();
$id = (int)($_GET['id'] ?? 0);
$row = null;
if ($id > 0) {
 $row = user_product((int)$user['id'], $id);
 if (!$row) { http_response_code(404); } }
$barcode = $row['barcode'] ?? clean_barcode((string)($_GET['barcode'] ?? ''));
$defaultName = (string)($row['custom_name'] ?? '');
if ($defaultName === '' && $barcode !== '') {
 $product = db_one('SELECT name FROM products WHERE barcode = ?', [$barcode]);
 $defaultName = trim((string)($product['name'] ?? '')); }
$pageTitle = $row ? 'تعديل منتج' : 'إضافة منتج';
$pageId = 'product-form';
require __DIR__ . '/includes/header.php';
if ($id > 0 && !$row) { ?>
<h1>تعديل منتج</h1>
<p>المنتج غير موجود.</p>
<?php } else { ?>
<h1><?php echo $row ? 'تعديل منتج' : 'إضافة منتج'; ?></h1>
<form id="product-form" class="field-grid" data-action="product_save">
<input type="hidden" name="id" value="<?php echo $row ? (int)$row['id'] : 0; ?>">
<input type="hidden" name="barcode" value="<?php echo h((string)$barcode); ?>">
<div>
<label for="custom_name">اسم المنتج</label>
<input id="custom_name" name="custom_name" required maxlength="120" value="<?php echo h($defaultName); ?>">
</div>
<div>
<label for="expiry_date">تاريخ انتهاء الصلاحية</label>
<input id="expiry_date" name="expiry_date" type="date" required value="<?php echo h((string)($row['expiry_date'] ?? '')); ?>">
</div>
<div>
<label for="opened_date">تاريخ الفتح (اختياري)</label>
<input id="opened_date" name="opened_date" type="date" value="<?php echo h((string)($row['opened_date'] ?? '')); ?>">
</div>
<div>
<label for="pao_months">مدة الاستخدام بعد الفتح بالأشهر (اختياري)</label>
<input id="pao_months" name="pao_months" inputmode="numeric" value="<?php echo h((string)($row['pao_months'] ?? '')); ?>">
</div>
<div class="span-2">
<label for="notes">ملاحظات (اختياري)</label>
<textarea id="notes" name="notes" maxlength="500"><?php echo h((string)($row['notes'] ?? '')); ?></textarea>
<p id="form-message" class="msg" role="alert"></p>
<button type="submit">حفظ</button>
</div>
</form>
<?php } ?>
<?php require __DIR__ . '/includes/footer.php';
