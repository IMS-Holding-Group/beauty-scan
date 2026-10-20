<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
$user = current_user();
if ($user) { header('Location: ' . ((int)$user['onboarding_done'] === 1 ? '/dashboard.php' : '/onboarding.php')); exit; }
$next = safe_next((string)($_GET['next'] ?? ''));
$pageTitle = 'تسجيل الدخول';
$pageId = 'login';
require __DIR__ . '/includes/header.php';
?>
<div class="split">
<div class="split-pane">
<h1>تسجيل الدخول</h1>
<form id="auth-form" data-action="login">
<label for="username">اسم المستخدم</label>
<input id="username" name="username" autocomplete="username" required autofocus>
<label for="password">كلمة السر</label>
<div class="inline-field">
<input id="password" name="password" type="password" autocomplete="current-password" required>
<button type="button" class="btn-ghost" data-toggle="password"><i class="fa-solid fa-eye"></i><span>إظهار</span></button>
</div>
<input type="hidden" name="next" value="<?php echo h($next); ?>">
<p id="form-message" class="msg" role="alert"></p>
<button type="submit">دخول</button>
</form>
<p><a href="/register.php">تسجيل حساب جديد</a></p>
</div>
<div class="split-visual"><img class="split-photo" src="/assets/images/makeup-shelf.jpg" alt="أدوات مكياج مرتبة على قماش"></div>
</div>
<?php require __DIR__ . '/includes/footer.php';
