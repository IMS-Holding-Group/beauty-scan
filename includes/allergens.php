<?php
declare(strict_types=1);

function add_allergens(int $userId, array $names): array {
 $catalog = catalog_all();
 $added = 0;
 $skipped = 0;
 foreach ($names as $rawName) {
  $name = trim((string)$rawName);
  $length = mb_strlen($name, 'UTF-8');
  if ($length < 2 || $length > 80) { $skipped += 1; continue; }
  $norm = normalize_text($name);
  if (mb_strlen($norm, 'UTF-8') < 2) { $skipped += 1; continue; }
  $ingredientId = null;
  foreach ($catalog as $row) {
   if (in_array($norm, ingredient_needles($row), true)) {
    $ingredientId = (int)$row['id'];
    $name = (string)$row['inci_name'];
    $norm = normalize_text($name);
    break; } }
  try {
   db_insert('INSERT INTO user_allergens (user_id, name, normalized_name, ingredient_id) VALUES (?, ?, ?, ?)', [$userId, $name, $norm, $ingredientId]);
   $added += 1; }
  catch (PDOException $error) {
   if ((string)$error->getCode() !== '23000') { throw $error; }
   $skipped += 1; } }
 return [$added, $skipped]; }

function delete_allergen(int $userId, int $id): bool {
 return db_exec('DELETE FROM user_allergens WHERE id = ? AND user_id = ?', [$id, $userId]) > 0; }

function list_allergens(int $userId): array {
 return db_all('SELECT id, name, normalized_name FROM user_allergens WHERE user_id = ? ORDER BY name', [$userId]); }

function finish_onboarding(int $userId): void {
 db_exec('UPDATE users SET onboarding_done = 1 WHERE id = ?', [$userId]); }
