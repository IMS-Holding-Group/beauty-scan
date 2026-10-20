<?php
declare(strict_types=1);
$pageTitle = $pageTitle ?? 'Beauty Scan';
$pageId = $pageId ?? '';
$user = current_user();
$unread = $user ? unread_count((int)$user['id']) : 0;
$logo = is_file(app_root() . '/assets/images/logo.png');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="<?php echo h(current_csrf()); ?>">
<title><?php echo h($pageTitle); ?></title>
<link rel="icon" href="/assets/images/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="/assets/css/style.css">
<link rel="stylesheet" href="/assets/vendor/fontawesome/icons.css">
</head>
<body data-page="<?php echo h($pageId); ?>">
<header class="site-header">
<div class="wrap header-row">
<a class="brand" href="<?php echo $user && (int)$user['onboarding_done'] === 1 ? '/dashboard.php' : '/'; ?>">
<?php if ($logo) { ?><img src="/assets/images/logo.png" alt="Beauty Scan" class="logo"><?php } else { ?><span class="brand-name">Beauty Scan</span><?php } ?>
</a>
<button type="button" class="nav-toggle" id="nav-toggle" aria-expanded="false" aria-controls="site-nav"><i class="fa-solid fa-bars"></i> القائمة</button>
<nav class="nav" id="site-nav" aria-label="التنقل">
<?php if ($user && (int)$user['onboarding_done'] === 1) { ?>
<a href="/dashboard.php"<?php echo $pageId === 'dashboard' ? ' aria-current="page"' : ''; ?>><i class="fa-solid fa-gauge"></i> لوحة التحكم</a>
<a href="/scan.php"<?php echo $pageId === 'scan' ? ' aria-current="page"' : ''; ?>><i class="fa-solid fa-barcode"></i> المسح</a>
<a href="/products.php"<?php echo $pageId === 'products' ? ' aria-current="page"' : ''; ?>><i class="fa-solid fa-pump-soap"></i> منتجاتي</a>
<a href="/allergens.php"<?php echo $pageId === 'allergens' ? ' aria-current="page"' : ''; ?>><i class="fa-solid fa-triangle-exclamation"></i> حساسيتي</a>
<a href="/notifications.php"<?php echo $pageId === 'notifications' ? ' aria-current="page"' : ''; ?>><i class="fa-solid fa-bell"></i> التنبيهات <bdi class="count" id="unread-count" dir="ltr"><?php echo (int)$unread; ?></bdi></a>
<a href="/account.php"<?php echo $pageId === 'account' ? ' aria-current="page"' : ''; ?>><i class="fa-solid fa-user"></i> حسابي</a>
<?php if ((int)($user['is_admin'] ?? 0) === 1) { ?><a href="/admin.php"<?php echo $pageId === 'admin' ? ' aria-current="page"' : ''; ?>><i class="fa-solid fa-shield-halved"></i> الإدارة</a><?php } ?>
<button type="button" id="logout-button"><i class="fa-solid fa-arrow-right-from-bracket"></i> خروج</button>
<?php } elseif ($user) { ?>
<button type="button" id="logout-button"><i class="fa-solid fa-arrow-right-from-bracket"></i> خروج</button>
<?php } else { ?>
<a href="/login.php"<?php echo $pageId === 'login' ? ' aria-current="page"' : ''; ?>><i class="fa-solid fa-right-to-bracket"></i> دخول</a>
<a href="/register.php"<?php echo $pageId === 'register' ? ' aria-current="page"' : ''; ?>><i class="fa-solid fa-user-plus"></i> تسجيل حساب</a>
<?php } ?>
</nav>
</div>
</header>
<?php
$mainClass = 'wrap page-in';
if ($pageId === 'home') { $mainClass = 'page-home'; }
if ($pageId === 'login' || $pageId === 'register') { $mainClass = 'page-auth'; }
?>
<main id="content" class="<?php echo $mainClass; ?>">
