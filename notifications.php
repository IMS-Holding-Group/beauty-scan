<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require_ready();
$user = current_user();
$rows = list_notifications((int)$user['id']);
$pageTitle = 'التنبيهات';
$pageId = 'notifications';
require __DIR__ . '/includes/header.php';
?>
<h1>التنبيهات</h1>
<p class="row-actions"><button type="button" id="notify-permission"><i class="fa-solid fa-bell"></i> تفعيل تنبيهات المتصفح</button> <button type="button" id="read-all" class="btn-ghost"><i class="fa-solid fa-check"></i> تعليم الكل كمقروء</button></p>
<?php if ($rows === []) { ?>
<p>لا توجد تنبيهات.</p>
<?php } else { ?>
<div class="table-wrap">
<table class="data-table">
<thead><tr><th>التنبيه</th><th>التاريخ الفعلي</th><th>الوقت</th><th>إجراء</th></tr></thead>
<tbody>
<?php foreach ($rows as $row) {
 $name = trim((string)($row['custom_name'] ?? '')) !== '' ? (string)$row['custom_name'] : 'منتج';
 $parts = null;
 $reason = '';
 if (!empty($row['expiry_date'])) {
  $pao = $row['pao_months'] === null || $row['pao_months'] === '' ? null : (int)$row['pao_months'];
  [$effective, $reasonKey] = effective_expiry((string)$row['expiry_date'], $row['opened_date'] ?: null, $pao);
  $parts = date_parts($effective);
  $reason = reason_text($reasonKey); }
 ?>
<tr class="<?php echo (int)$row['is_read'] === 1 ? 'is-read' : ''; ?>">
<td data-label="التنبيه">
<p class="primary-text"><?php echo h(notification_lead((string)$row['type'], $name)); ?></p>
<?php if ($reason !== '') { ?><p class="muted"><?php echo h($reason); ?></p><?php } ?>
</td>
<td data-label="التاريخ الفعلي"><?php echo $parts ? show_date($parts) : ''; ?></td>
<td data-label="الوقت"><?php echo show_datetime(datetime_parts((string)$row['created_at'])); ?></td>
<td data-label="إجراء"><?php if ((int)$row['is_read'] !== 1) { ?><button type="button" class="btn-ghost" data-read="<?php echo (int)$row['id']; ?>"><i class="fa-solid fa-check"></i> تعليم كمقروء</button><?php } ?></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
<?php } ?>
<?php require __DIR__ . '/includes/footer.php';
