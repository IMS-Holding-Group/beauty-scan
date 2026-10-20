<?php
declare(strict_types=1);

function ingredient_needles(array $row): array {
 $keys = [normalize_text((string)$row['inci_name']), normalize_text((string)$row['arabic_name'])];
 foreach (explode(',', (string)($row['synonyms'] ?? '')) as $synonym) {
  $norm = normalize_text($synonym);
  if ($norm !== '') { $keys[] = $norm; } }
 $out = [];
 foreach ($keys as $key) {
  if (mb_strlen($key, 'UTF-8') >= 2) { $out[$key] = true; } }
 return array_keys($out); }

function explain_ingredients(string $text, array $catalog): array {
 $prepared = [];
 foreach ($catalog as $row) {
  $prepared[] = [$row, ingredient_needles($row)]; }
 $result = [];
 foreach (split_parts($text) as $segment) {
  $norm = normalize_text($segment);
  $best = null;
  $bestLen = 0;
  foreach ($prepared as [$row, $keys]) {
   foreach ($keys as $key) {
    if (($key === $norm || str_contains($norm, $key)) && mb_strlen($key, 'UTF-8') > $bestLen) {
     $best = $row;
     $bestLen = mb_strlen($key, 'UTF-8'); } } }
  if ($best) {
   $result[] = [
    'raw' => $segment,
    'known' => true,
    'arabic_name' => (string)$best['arabic_name'],
    'function_ar' => (string)$best['function_ar'],
    'notes_ar' => (string)($best['notes_ar'] ?? ''),
   ]; }
  else {
   $result[] = [
    'raw' => $segment,
    'known' => false,
    'arabic_name' => '',
    'function_ar' => '',
    'notes_ar' => '',
   ]; } }
 return $result; }

function match_allergens(string $text, array $allergens, array $catalog): array {
 if (trim($text) === '') { return ['status' => 'missing', 'names' => []]; }
 $byId = [];
 foreach ($catalog as $row) { $byId[(int)$row['id']] = $row; }
 $found = [];
 $seen = [];
 foreach (split_parts($text) as $segment) {
  $norm = normalize_text($segment);
  foreach ($allergens as $allergen) {
   $partial = empty($allergen['ingredient_id']);
   $needles = [];
   if (!$partial && isset($byId[(int)$allergen['ingredient_id']])) {
    $needles = ingredient_needles($byId[(int)$allergen['ingredient_id']]); }
   else {
    $free = (string)($allergen['normalized_name'] ?? '');
    if (mb_strlen($free, 'UTF-8') >= 2) { $needles[] = $free; } }
   foreach ($needles as $needle) {
    $hit = $needle === $norm || str_contains($norm, $needle);
    if ($partial && mb_strlen($norm, 'UTF-8') >= 4 && str_contains($needle, $norm)) { $hit = true; }
    if (!$hit) { continue; }
    $label = original_slice($segment, $needle);
    $key = normalize_text($label);
    if ($key !== '' && !isset($seen[$key])) {
     $seen[$key] = true;
     $found[] = $label; }
    break; } } }
 if ($found === []) { return ['status' => 'clear', 'names' => []]; }
 return ['status' => 'hit', 'names' => $found]; }
