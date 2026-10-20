<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require_ready();
$user = current_user();
$code = clean_barcode((string)($_GET['barcode'] ?? ''));
$pageTitle = 'نتيجة المنتج';
$pageId = 'product';
require __DIR__ . '/includes/header.php';
if ($code === '') { ?>
<h1>نتيجة المنتج</h1>
<p>أدخل رقم الباركود من صفحة المسح.</p>
<?php } else {
 $view = ensure_product($code, (int)$user['id']);
 $product = $view['product'];
 $name = trim((string)($product['name'] ?? ''));
 ?>
<h1 class="verdict-<?php echo $view['verdict'] === 'trusted' ? 'trusted' : 'needs'; ?>"><?php echo h($view['verdict_text']); ?></h1>
<ul>
<?php foreach ($view['reasons'] as $reason) { ?><li><?php echo h((string)$reason); ?></li><?php } ?>
</ul>
<?php if ($view['allergy']['status'] === 'missing') { ?>
<p class="alert alert-warn">تعذر فحص الحساسية بسبب غياب بيانات المكونات.</p>
<?php } elseif ($view['allergy']['status'] === 'hit') { ?>
<div class="alert alert-bad" role="alert">
<p class="primary-text">تنبيه: وُجدت مواد مسجلة في حساسيتك ضمن مكونات هذا المنتج.</p>
<ul>
<?php foreach ($view['allergy']['names'] as $hit) { ?><li><?php echo h((string)$hit); ?></li><?php } ?>
</ul>
</div>
<?php } else { ?>
<p class="alert alert-ok">لم يُعثر على المواد المسجلة في حساسيتك ضمن مكونات هذا المنتج.</p>
<?php } ?>
<?php $photo = product_photo($view['barcode']); if ($photo !== '') { ?><img class="product-hero" src="<?php echo h($photo); ?>" alt="<?php echo h($name !== '' ? $name : 'منتج'); ?>"><?php } ?>
<section class="panel">
<h2><?php echo h($name !== '' ? $name : 'منتج بدون اسم'); ?></h2>
<p>الباركود: <?php echo h($view['barcode']); ?></p>
<p>العلامة: <?php echo h(trim((string)($product['brand'] ?? '')) !== '' ? (string)$product['brand'] : 'غير متوفرة'); ?></p>
<p>الفئة: <?php echo h(trim((string)($product['category'] ?? '')) !== '' ? (string)$product['category'] : 'غير متوفرة'); ?></p>
</section>
<h2>المكونات</h2>
<?php if ($view['explanations'] === []) { ?>
<p>لا توجد قائمة مكونات.</p>
<?php } else { ?>
<ul class="ings">
<?php foreach ($view['explanations'] as $item) { ?>
<li>
<span class="primary-text"><?php echo h((string)$item['raw']); ?></span>
<?php if ($item['known']) { ?>
<span><?php echo h((string)$item['arabic_name']); ?></span>
<span><?php echo h((string)$item['function_ar']); ?></span>
<?php if ($item['notes_ar'] !== '') { ?><span class="muted"><?php echo h((string)$item['notes_ar']); ?></span><?php } ?>
<?php } else { ?>
<span class="muted">لا تتوفر معلومات مبسطة</span>
<?php } ?>
</li>
<?php } ?>
</ul>
<?php } ?>
<p><a class="btn" href="/product_form.php?barcode=<?php echo h($view['barcode']); ?>">إضافة إلى منتجاتي</a></p>
<p class="disclaimer">الحكم مبني على مطابقة البيانات في قواعد البيانات ولا يثبت أصالة المنتج المادي. المعلومات إرشادية ولا تغني عن استشارة الطبيب المختص.</p>
<?php } ?>
<?php require __DIR__ . '/includes/footer.php';
