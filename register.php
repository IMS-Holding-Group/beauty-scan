<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
$user = current_user();
if ($user) { header('Location: ' . ((int)$user['onboarding_done'] === 1 ? '/dashboard.php' : '/onboarding.php')); exit; }
$pageTitle = 'تسجيل حساب';
$pageId = 'register';
require __DIR__ . '/includes/header.php';
?>
<div class="split">
<div class="split-pane">
<h1>تسجيل حساب</h1>
<form id="auth-form" data-action="register">
<label for="username">اسم المستخدم</label>
<input id="username" name="username" autocomplete="username" required autofocus>
<label for="display_name">الاسم المعروض</label>
<input id="display_name" name="display_name" autocomplete="name" required>
<label for="email">البريد (اختياري)</label>
<input id="email" name="email" type="email" autocomplete="email">
<label for="password">كلمة السر</label>
<div class="inline-field">
<input id="password" name="password" type="password" autocomplete="new-password" required>
<button type="button" class="btn-ghost" data-toggle="password"><i class="fa-solid fa-eye"></i><span>إظهار</span></button>
</div>
<p id="strength" class="muted"></p>
<label for="confirm">تأكيد كلمة السر</label>
<input id="confirm" name="confirm" type="password" autocomplete="new-password" required>
<p class="muted">8 أحرف على الأقل.</p>
<p id="form-message" class="msg" role="alert"></p>
<button type="submit">إنشاء الحساب</button>
</form>
<p><a href="/login.php">لديك حساب</a></p>
</div>
<div class="split-visual"><img class="split-photo" src="/assets/images/texture-cream.jpg" alt="ملمس كريم على طبق خزفي"></div>
</div>
<?php require __DIR__ . '/includes/footer.php';
