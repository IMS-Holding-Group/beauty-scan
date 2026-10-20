<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require_ready();
$user = current_user();
$pageTitle = 'حسابي';
$pageId = 'account';
require __DIR__ . '/includes/header.php';
?>
<h1>حسابي</h1>
<div class="layout">
<section>
<h2>بيانات الحساب</h2>
<div class="table-wrap">
<table class="data-table">
<tbody>
<tr><th>اسم المستخدم</th><td><?php echo h((string)$user['username']); ?></td></tr>
<tr><th>تاريخ التسجيل</th><td><?php echo show_datetime(datetime_parts((string)$user['created_at'])); ?></td></tr>
</tbody>
</table>
</div>
<form id="account-form" data-action="account">
<label for="display_name">الاسم المعروض</label>
<input id="display_name" name="display_name" required maxlength="60" value="<?php echo h((string)$user['display_name']); ?>">
<label for="email">البريد</label>
<input id="email" name="email" type="email" maxlength="120" value="<?php echo h((string)$user['email']); ?>">
<label for="notice_days">أيام التنبيه المسبق</label>
<input id="notice_days" name="notice_days" inputmode="numeric" required value="<?php echo (int)$user['notice_days']; ?>">
<label for="current_password">كلمة السر الحالية عند تغيير البريد</label>
<input id="current_password" name="current_password" type="password" autocomplete="current-password">
<p id="form-message" class="msg" role="alert"></p>
<button type="submit">حفظ</button>
</form>
</section>
<section>
<h2>تغيير كلمة السر</h2>
<form id="password-form" data-action="password">
<label for="password_current">كلمة السر الحالية</label>
<input id="password_current" name="current_password" type="password" autocomplete="current-password" required>
<label for="password_next">كلمة السر الجديدة</label>
<input id="password_next" name="password" type="password" autocomplete="new-password" required>
<label for="password_confirm">تأكيد كلمة السر</label>
<input id="password_confirm" name="confirm" type="password" autocomplete="new-password" required>
<p id="password-message" class="msg" role="alert"></p>
<button type="submit">تغيير كلمة السر</button>
</form>
</section>
</div>
<?php require __DIR__ . '/includes/footer.php';
