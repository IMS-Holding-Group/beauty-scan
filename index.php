<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
$user = current_user();
if ($user && (int)$user['onboarding_done'] !== 1) { header('Location: /onboarding.php'); exit; }
$pageTitle = 'Beauty Scan';
$pageId = 'home';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
<div class="wrap hero-grid">
<div class="hero-copy">
<p class="eyebrow">مكياج وعناية بالبشرة</p>
<h1>راجع المنتج قبل ما تستخدمه</h1>
<p>امسح الباركود، اقرأ المكونات بشرح واضح، وطابقها مع حساسيتك، وتابع صلاحية ما عندك.</p>
<p class="hero-actions">
<?php if ($user) { ?>
<a class="btn" href="/dashboard.php">لوحة التحكم</a>
<a class="btn btn-ghost" href="/scan.php">مسح منتج</a>
<?php } else { ?>
<a class="btn" href="/register.php">تسجيل حساب</a>
<a class="btn btn-ghost" href="/login.php">دخول</a>
<?php } ?>
</p>
<p class="muted">الحكم مطابقة لبيانات مسجلة، وليس فحصاً للعبوة نفسها.</p>
</div>
<img src="/assets/images/hero-care.jpg" alt="زجاجات عناية بالبشرة على سطح حجري">
</div>
</section>
<section class="section band">
<div class="wrap">
<h2>ما الذي تراجعه</h2>
<div class="grid-3">
<article class="card">
<img src="/assets/images/texture-cream.jpg" alt="ملمس كريم على طبق خزفي">
<div class="card-body">
<h3>المكونات</h3>
<p>الاسم العربي والوظيفة المبسطة لكل مكوّن معروف في المنتج.</p>
</div>
</article>
<article class="card">
<img src="/assets/images/makeup-shelf.jpg" alt="أدوات مكياج مرتبة على قماش">
<div class="card-body">
<h3>الحساسية</h3>
<p>مقارنة مكونات المنتج بالمواد التي سجلتها، بالاسم كما هو مكتوب.</p>
</div>
</article>
<article class="card">
<img src="/assets/images/scan-hand.jpg" alt="يد تمسك جهازاً فوق باركود عبوة">
<div class="card-body">
<h3>الصلاحية</h3>
<p>التاريخ الأقرب بين المطبوع ومدة ما بعد الفتح، مع تنبيه قبل الانتهاء.</p>
</div>
</article>
</div>
</div>
</section>
<section class="section">
<div class="wrap">
<h2>من المسح إلى التنبيه</h2>
<ol class="steps">
<li><b>1</b><span>امسح الباركود أو اكتبه إذا تعذرت الكاميرا.</span></li>
<li><b>2</b><span>راجع الاسم والعلامة والمكونات والحكم.</span></li>
<li><b>3</b><span>احفظ المنتج مع تاريخ الصلاحية.</span></li>
<li><b>4</b><span>يصلك تنبيه قبل الانتهاء وبعده.</span></li>
</ol>
</div>
</section>
<section class="closing">
<div class="wrap closing-row">
<div>
<h2>ابدأ من منتج واحد</h2>
<p>الحساب يحفظ حساسيتك ومنتجاتك وتنبيهاتك.</p>
</div>
<?php if ($user) { ?>
<a class="btn" href="/products.php">منتجاتي</a>
<?php } else { ?>
<a class="btn" href="/register.php">إنشاء حساب</a>
<?php } ?>
</div>
</section>
<?php require __DIR__ . '/includes/footer.php';
