<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require_login();
$user = current_user();
if ((int)$user['onboarding_done'] === 1) { header('Location: /dashboard.php'); exit; }
$pageTitle = 'إعداد الحساسية';
$pageId = 'allergens';
$finish = 1;
$showSkip = true;
require __DIR__ . '/includes/header.php';
?>
<h1>إعداد الحساسية</h1>
<p>سجل المواد التي تتحسس منها، أو تخطَّ هذه الخطوة.</p>
<?php require __DIR__ . '/includes/allergen_form.php'; ?>
<?php require __DIR__ . '/includes/footer.php';
