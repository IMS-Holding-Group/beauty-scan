<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require_ready();
$pageTitle = 'المسح';
$pageId = 'scan';
require __DIR__ . '/includes/header.php';
?>
<h1>مسح الباركود</h1>
<div class="layout">
<video id="camera" autoplay playsinline muted></video>
<form id="manual-form">
<label for="barcode-input">رقم الباركود</label>
<input id="barcode-input" inputmode="numeric" autocomplete="off" required>
<p id="manual-reason" class="msg" role="status"></p>
<p id="form-message" class="msg" role="alert"></p>
<button type="submit">بحث</button>
</form>
</div>
<?php require __DIR__ . '/includes/footer.php';
