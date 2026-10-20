<?php
declare(strict_types=1);

function demo_barcodes(): array {
 $codes = [];
 for ($i = 1; $i <= 15; $i++) {
  $codes[] = make_ean13('6281000000' . str_pad((string)$i, 2, '0', STR_PAD_LEFT)); }
 $codes[] = '96385074';
 $codes[] = make_upca('12345678901');
 return $codes; }

function ingredient_rows(): array {
 return [
  ['Aqua', 'ماء', 'مذيب يشكل قاعدة المستحضر', 'المكوّن الأكثر شيوعاً في التركيبات المائية.', 'water, eau'],
  ['Glycerin', 'جلسرين', 'مرطّب يجذب الماء', 'يُستخدم لتخفيف جفاف البشرة.', 'glycerine, glycerol'],
  ['Parfum', 'عطر', 'يمنح رائحة للمنتج', 'قد يهيّج البشرة الحساسة.', 'fragrance, perfume, aroma, عطر'],
  ['Limonene', 'ليمونين', 'مكوّن عطري', 'من المواد العطرية الواجب الإفصاح عنها.', 'd-limonene'],
  ['Linalool', 'لينالول', 'مكوّن عطري', 'من المكونات العطرية المعلنة شائعاً.', ''],
  ['Citronellol', 'سيترونيلول', 'مكوّن عطري', 'يُذكر ضمن مسببات الحساسية العطرية.', ''],
  ['Geraniol', 'جيرانيول', 'مكوّن عطري', 'من المواد العطرية المعلنة.', ''],
  ['Eugenol', 'يوجينول', 'مكوّن عطري', 'يوجد في بعض الزيوت مثل القرنفل.', ''],
  ['Cinnamal', 'سينامال', 'مكوّن عطري', 'مرتبط برائحة القرفة وقد يهيّج البشرة الحساسة.', ''],
  ['Coumarin', 'كومارين', 'مكوّن عطري', 'يُعلن ضمن قائمة العطور المسببة للحساسية.', ''],
  ['Citral', 'سيترال', 'مكوّن عطري', 'يوجد في بعض الزيوت الحمضية.', ''],
  ['Alcohol Denat', 'كحول معدل', 'مذيب وقابض', 'قد يجفف البشرة عند التركيز العالي.', 'alcohol denat., denatured alcohol, sd alcohol'],
  ['Phenoxyethanol', 'فينوكسي إيثانول', 'حافظ يمنع نمو الجراثيم', 'من الحافظات الشائعة بتركيز منخفض.', ''],
  ['Methylparaben', 'ميثيل بارابين', 'حافظ', 'من عائلة البارابين.', 'methyl paraben'],
  ['Propylparaben', 'بروبيل بارابين', 'حافظ', 'من عائلة البارابين.', 'propyl paraben'],
  ['Methylisothiazolinone', 'ميثيل إيزوثيازولينون', 'حافظ', 'قد يسبب تحسساً تماسياً لدى بعض الأشخاص.', 'methylisothiazolinone'],
  ['Formaldehyde', 'فورمالدهايد', 'حافظ', 'مادة مهيّجة، وقد تتحرر من بعض الحافظات.', 'formalin'],
  ['Benzyl Alcohol', 'كحول بنزيلي', 'حافظ ومذيب عطري', 'يُستخدم بتركيز منخفض.', ''],
  ['Propylene Glycol', 'بروبيلين غليكول', 'مرطّب ومذيب', 'قد يسبب تهيّجاً لقلة من المستخدمين.', ''],
  ['Sodium Lauryl Sulfate', 'لوريل كبريتات الصوديوم', 'منظف رغوي', 'قد يجفف البشرة وفروة الرأس.', 'sls'],
  ['Cocamidopropyl Betaine', 'كوكاميدوبروبيل بيتين', 'منظف لطيف مساعد للرغوة', 'يُشتق من جوز الهند.', 'capb'],
  ['Lanolin', 'لانولين', 'مرطّب دهني', 'يُستخلص من صوف الغنم وقد يتحسس منه بعض الأشخاص.', ''],
  ['Tocopherol', 'توكوفيرول', 'مضاد أكسدة', 'شكل من فيتامين هـ.', 'vitamin e'],
  ['Retinol', 'ريتينول', 'يجدد مظهر البشرة', 'شكل من فيتامين أ وقد يسبب تقشراً في البداية.', 'vitamin a'],
  ['Salicylic Acid', 'حمض الساليسيليك', 'مقشر ينظف المسام', 'يُستخدم في منتجات الحبوب.', 'salicylic acid'],
  ['Niacinamide', 'نياسيناميد', 'يدعم حاجز البشرة', 'شكل من فيتامين ب3.', 'nicotinamide, vitamin b3'],
  ['Hyaluronic Acid', 'حمض الهيالورونيك', 'مرطّب يحتفظ بالماء', 'يوجد أيضاً بصورة Sodium Hyaluronate.', 'sodium hyaluronate'],
  ['Titanium Dioxide', 'ثاني أكسيد التيتانيوم', 'واقي فيزيائي وملوّن', 'يُعرف أيضاً بالرمز CI 77891.', 'ci 77891, titanium dioxide'],
  ['Zinc Oxide', 'أكسيد الزنك', 'واقي فيزيائي من الشمس', 'يبقى على سطح البشرة.', 'ci 77947'],
  ['Benzophenone-3', 'بنزوفينون-3', 'واقي كيميائي من الشمس', 'يُعرف باسم أوكسي بنزون.', 'oxybenzone'],
  ['Butyl Methoxydibenzoylmethane', 'أفو بنزون', 'واقي من أشعة UVA', 'يُعرف باسم أفو بنزون.', 'avobenzone'],
  ['Butyrospermum Parkii Butter', 'زبدة الشيا', 'مرطّب دهني مغذ', 'تُعرف باسم Shea Butter.', 'shea butter'],
  ['Cocos Nucifera Oil', 'زيت جوز الهند', 'زيت مرطّب', 'قد لا يناسب البشرة المعرضة للحبوب.', 'coconut oil'],
  ['Prunus Amygdalus Dulcis Oil', 'زيت اللوز الحلو', 'زيت مرطّب', 'يُستخدم لتليين الجلد.', 'sweet almond oil'],
  ['Melaleuca Alternifolia Leaf Oil', 'زيت شجرة الشاي', 'زيت عطري', 'يُستخدم في منتجات البشرة المعرضة للحبوب.', 'tea tree oil'],
  ['Cera Alba', 'شمع العسل', 'مكثّف ومرطّب', 'قد يتحسس منه بعض الأشخاص.', 'beeswax'],
  ['Propolis', 'عكبر', 'مكوّن من منتجات النحل', 'قد يتحسس منه من يتحسس من منتجات النحل.', 'bee propolis'],
  ['CI 75470', 'كارمين', 'ملوّن أحمر', 'قد يتحسس منه بعض الأشخاص.', 'carmine, cochineal'],
  ['Mica', 'ميكا', 'ملوّن لؤلؤي', 'يمنح لمعة في مكياج العيون والوجه.', ''],
  ['Dimethicone', 'دايميثيكون', 'سيليكون يمنع تبخر الماء', 'يمنح ملمساً ناعماً.', ''],
  ['Butylene Glycol', 'بوتيلين غليكول', 'مرطّب ومذيب', 'بديل شائع للبروبيلين غليكول.', ''],
  ['Caprylic/Capric Triglyceride', 'ثلاثي الغليسريد الكابريلي', 'مرطّب خفيف', 'يُشتق من زيت جوز الهند.', 'caprylic capric triglyceride'],
  ['Cetyl Alcohol', 'كحول سيتيلي', 'مكثّف دهني', 'كحول دهني غير مجفف.', ''],
  ['Stearyl Alcohol', 'كحول ستياريلي', 'مكثّف دهني', 'يلين قوام الكريم.', ''],
  ['Cetearyl Alcohol', 'كحول سيتاريلي', 'مكثّف دهني', 'مزيج من كحولين دهنيين.', ''],
  ['Sodium Hydroxide', 'هيدروكسيد الصوديوم', 'ضابط حموضة', 'يُستخدم بكمية ضئيلة لضبط الحموضة.', ''],
  ['Citric Acid', 'حمض الستريك', 'ضابط حموضة', 'يضبط درجة حموضة المستحضر.', ''],
  ['Disodium EDTA', 'ثنائي صوديوم EDTA', 'مثبت يرتبط بالمعادن', 'يدعم عمل الحافظات.', 'edta'],
  ['BHT', 'بي إتش تي', 'مضاد أكسدة للزيوت', 'يمنع تزنخ الزيوت.', 'butylated hydroxytoluene'],
  ['BHA', 'بي إتش إيه', 'مضاد أكسدة', 'هنا بمعنى Butylated Hydroxyanisole.', 'butylated hydroxyanisole'],
  ['Talc', 'تلك', 'ممتص للرطوبة', 'يُستخدم في البودرة.', ''],
  ['Isopropyl Myristate', 'إيزوبروبيل ميريستات', 'مرطّب خفيف', 'قد لا يناسب البشرة المعرضة للحبوب.', ''],
  ['Paraffinum Liquidum', 'زيت معدني', 'مرطّب عازل', 'يُعرف أيضاً بـ Mineral Oil.', 'mineral oil'],
  ['Petrolatum', 'فازلين', 'مرطّب عازل', 'يقلل فقدان الماء من البشرة.', 'petroleum jelly'],
  ['Panthenol', 'بانثينول', 'مرطّب مهدئ', 'يتحول في الجلد إلى فيتامين ب5.', 'provitamin b5, d-panthenol'],
  ['Allantoin', 'ألانتوين', 'مهدئ للبشرة', 'يُستخدم في كريمات التهدئة.', ''],
  ['Centella Asiatica Extract', 'خلاصة السنتيلا', 'مهدئ داعم لمظهر البشرة', 'تُعرف أيضاً بـ Cica.', 'centella asiatica, cica'],
  ['Aloe Barbadensis Leaf Juice', 'عصير الصبار', 'مهدئ ومرطّب', 'من جل الألوفيرا.', 'aloe vera, aloe leaf juice'],
  ['Tocopheryl Acetate', 'أسيتات التوكوفيريل', 'مضاد أكسدة', 'شكل أكثر ثباتاً من فيتامين هـ.', ''],
  ['Ascorbic Acid', 'حمض الأسكوربيك', 'مضاد أكسدة', 'فيتامين ج، وقد يتهيج بعض البشرة من تركيزه العالي.', 'vitamin c'],
  ['Sodium Benzoate', 'بنزوات الصوديوم', 'حافظ', 'يعمل في الوسط الحمضي.', ''],
  ['Potassium Sorbate', 'سوربات البوتاسيوم', 'حافظ', 'يحد من الخمائر والفطريات.', ''],
  ['Chlorphenesin', 'كلورفينيسين', 'حافظ', 'يُستخدم بتركيز منخفض.', ''],
  ['Diazolidinyl Urea', 'ديازولدينايل يوريا', 'حافظ', 'قد يحرر كميات ضئيلة من الفورمالدهايد.', ''],
  ['DMDM Hydantoin', 'دي إم دي إم هيدانتوين', 'حافظ', 'قد يحرر كميات ضئيلة من الفورمالدهايد.', 'dmdm hydantoin'],
  ['Triethanolamine', 'ثلاثي إيثانول أمين', 'ضابط حموضة', 'يوازن درجة الحموضة.', ''],
  ['Sodium Laureth Sulfate', 'لوريث كبريتات الصوديوم', 'منظف رغوي', 'أقل قساوة من SLS عادة.', 'sles'],
  ['PEG-40 Hydrogenated Castor Oil', 'زيت خروع مهدرج', 'مذيب للزيوت في الماء', 'يُستخدم لخلط العطر.', 'peg 40 hydrogenated castor oil'],
  ['Alpha-Isomethyl Ionone', 'ألفا أيزوميثيل أيونون', 'مكوّن عطري', 'من المواد العطرية المعلنة.', 'alpha isomethyl ionone'],
  ['Benzyl Salicylate', 'ساليسيلات البنزيل', 'مكوّن عطري', 'من المواد العطرية المعلنة.', ''],
  ['Hexyl Cinnamal', 'هكسيل سينامال', 'مكوّن عطري', 'رائحة زهرية من المواد المعلنة.', ''],
  ['Hydroxycitronellal', 'هيدروكسي سيترونيلال', 'مكوّن عطري', 'من المواد العطرية المعلنة.', ''],
  ['Isoeugenol', 'أيزويوجينول', 'مكوّن عطري', 'من المواد العطرية المعلنة.', ''],
  ['Benzyl Benzoate', 'بنزوات البنزيل', 'مذيب عطري', 'من المواد العطرية المعلنة.', ''],
  ['CI 77491', 'أكسيد الحديد', 'ملوّن', 'يمنح درجات حمراء وبنية.', 'iron oxides, ci 77492, ci 77499'],
  ['Silica', 'سيليكا', 'ممتص وملمس مطفي', 'يقلل اللمعة.', ''],
  ['Xanthan Gum', 'صمغ الزانثان', 'مكثّف', 'يثبّت القوام.', ''],
  ['Carbomer', 'كاربومر', 'مكثّف هلامي', 'يمنح قوام الجل.', ''],
  ['Ethylhexylglycerin', 'إيثيل هكسيل جلسرين', 'داعم للحفظ ومرطّب', 'غالباً مع الفينوكسي إيثانول.', ''],
  ['Caprylyl Glycol', 'كابريليل غليكول', 'مرطّب وداعم للحفظ', 'يُستخدم في الحافظات اللطيفة.', ''],
  ['Ricinus Communis Seed Oil', 'زيت الخروع', 'زيت لامع', 'شائع في أحمر الشفاه.', 'castor oil'],
  ['Helianthus Annuus Seed Oil', 'زيت دوار الشمس', 'زيت مرطّب', 'خفيف على البشرة.', 'sunflower oil'],
  ['Simmondsia Chinensis Seed Oil', 'زيت الجوجوبا', 'زيت مرطّب', 'قريب من دهون البشرة.', 'jojoba oil'],
  ['Sodium Chloride', 'كلوريد الصوديوم', 'مكثّف للغسول', 'ملح يضبط اللزوجة.', 'salt'],
  ['Hamamelis Virginiana Leaf Extract', 'خلاصة الهاماميليس', 'قابض مهدئ', 'يُعرف بماء الساحرة.', 'witch hazel'],
 ]; }

function product_rows(): array {
 return [
  ['منتج تجريبي غسول يومي', 'علامة تجريبية', 'غسول', 'Aqua, Glycerin, Sodium Lauryl Sulfate, Cocamidopropyl Betaine, Parfum, Limonene, Citric Acid', 1],
  ['منتج تجريبي سيروم', 'علامة تجريبية', 'سيروم', 'Aqua, Hyaluronic Acid, Niacinamide, Glycerin, Phenoxyethanol', 1],
  ['منتج تجريبي واقي شمس', 'علامة تجريبية', 'واقي شمس', 'Zinc Oxide, Titanium Dioxide, Butyrospermum Parkii Butter, Cocos Nucifera Oil, Tocopherol', 1],
  ['منتج تجريبي أحمر شفاه', 'علامة تجريبية', 'مكياج', 'Ricinus Communis Seed Oil, Cera Alba, CI 75470, CI 15850, Tocopherol', 1],
  ['منتج تجريبي كريم مرطب', 'علامة تجريبية', 'مرطب', 'Aqua, Glycerin, Niacinamide, Panthenol, Dimethicone, Phenoxyethanol', 1],
  ['منتج تجريبي شامبو', 'علامة تجريبية', 'شعر', 'Aqua, Sodium Laureth Sulfate, Cocamidopropyl Betaine, Parfum, Limonene, Linalool, Citronellol, Geraniol', 1],
  ['منتج تجريبي زيت لطيف', 'علامة تجريبية', 'زيت', 'Prunus Amygdalus Dulcis Oil, Tocopherol', 1],
  ['منتج تجريبي كريم ليلي', 'علامة تجريبية', 'مرطب', 'Aqua, Retinol, Glycerin, Dimethicone, Phenoxyethanol', 1],
  ['منتج تجريبي مقشر', 'علامة تجريبية', 'مقشر', 'Aqua, Salicylic Acid, Glycerin, Alcohol Denat', 1],
  ['منتج تجريبي بلسم', 'علامة تجريبية', 'شعر', 'Aqua, Cetearyl Alcohol, Butyrospermum Parkii Butter, Panthenol', 1],
  ['منتج تجريبي ناقص المكونات', 'علامة تجريبية', 'عناية', '', 0],
  ['منتج تجريبي بلا علامة', '', 'عناية', '', 0],
  ['منتج تجريبي غير موثّق', 'علامة تجريبية', 'عناية', 'Aqua, Glycerin, Panthenol', 0],
  ['منتج تجريبي موثّق بلا مكونات', 'علامة تجريبية', 'عناية', '', 1],
  ['منتج تجريبي تونر', 'علامة تجريبية', 'تونر', 'Aqua, Glycerin, Hamamelis Virginiana Leaf Extract, Unknown Leaf Extract, Phenoxyethanol', 1],
  ['منتج تجريبي عبوة صغيرة', 'علامة تجريبية', 'عناية', 'Aqua, Glycerin, Allantoin', 1],
  ['منتج تجريبي بودرة', 'علامة تجريبية', 'مكياج', 'Talc, Mica, Silica, CI 77491', 1],
 ]; }

function seed_if_needed(): void {
 $ingredients = db_one('SELECT COUNT(*) AS c FROM ingredients');
 if ((int)($ingredients['c'] ?? 0) === 0) {
  foreach (ingredient_rows() as $row) {
   db_insert('INSERT INTO ingredients (inci_name, arabic_name, function_ar, notes_ar, synonyms) VALUES (?, ?, ?, ?, ?)', $row); } }
 $products = db_one('SELECT COUNT(*) AS c FROM products');
 if ((int)($products['c'] ?? 0) === 0) {
  $codes = demo_barcodes();
  $rows = product_rows();
  if (count($codes) !== count($rows)) { throw new RuntimeException('seed size'); }
  foreach ($rows as $index => $row) {
   db_insert('INSERT INTO products (barcode, name, brand, category, ingredients_text, source, is_verified, fetched_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', [
    $codes[$index], $row[0], $row[1], $row[2], $row[3], 'local_verified', $row[4], now_iso(),
   ]); } }
 seed_demo_users(); }

function seed_demo_users(): void {
 seed_one_demo(env('DEMO_EMPTY_USERNAME'), env('DEMO_EMPTY_PASSWORD'), 'مستخدم جديد', 0, false);
 seed_one_demo(env('DEMO_FILLED_USERNAME'), env('DEMO_FILLED_PASSWORD'), 'مستخدم تجريبي', 1, true);
 seed_admin(); }

function seed_admin(): void {
 $username = normalize_username(env('DEMO_ADMIN_USERNAME'));
 $password = env('DEMO_ADMIN_PASSWORD');
 if ($username === '' || $password === '') { return; }
 $exists = db_one('SELECT id FROM users WHERE username = ?', [$username]);
 if ($exists) {
  db_exec('UPDATE users SET is_admin = 1, onboarding_done = 1 WHERE id = ?', [(int)$exists['id']]);
  return; }
 db_insert('INSERT INTO users (username, display_name, email, password_hash, notice_days, onboarding_done, is_admin, created_at) VALUES (?, ?, NULL, ?, 30, 1, 1, ?)', [
  $username, 'مسؤول', password_hash($password, PASSWORD_BCRYPT), now_iso(),
 ]); }

function seed_one_demo(string $username, string $password, string $display, int $done, bool $filled): void {
 $username = normalize_username($username);
 if ($username === '' || $password === '') { return; }
 $exists = db_one('SELECT id FROM users WHERE username = ?', [$username]);
 if ($exists) { return; }
 $id = (int)db_insert('INSERT INTO users (username, display_name, email, password_hash, notice_days, onboarding_done, created_at) VALUES (?, ?, NULL, ?, 30, ?, ?)', [
  $username, $display, password_hash($password, PASSWORD_BCRYPT), $done, now_iso(),
 ]);
 if (!$filled) { return; }
 foreach (['Parfum', 'Limonene'] as $inci) {
  $row = db_one('SELECT id, inci_name FROM ingredients WHERE inci_name = ?', [$inci]);
  if (!$row) { continue; }
  db_insert('INSERT INTO user_allergens (user_id, name, normalized_name, ingredient_id) VALUES (?, ?, ?, ?)', [
   $id, $row['inci_name'], normalize_text((string)$row['inci_name']), (int)$row['id'],
  ]); }
 $codes = demo_barcodes();
 $today = today_riyadh();
 $plan = [
  [$codes[0], 'منتج تجريبي غسول يومي', $today->modify('+90 days')->format('Y-m-d'), null, null],
  [$codes[1], 'منتج تجريبي سيروم', $today->modify('+20 days')->format('Y-m-d'), null, null],
  [$codes[2], 'منتج تجريبي واقي شمس', $today->modify('+4 days')->format('Y-m-d'), null, null],
  [$codes[3], 'منتج تجريبي أحمر شفاه', $today->modify('-10 days')->format('Y-m-d'), null, null],
  [$codes[4], 'منتج تجريبي كريم مرطب', $today->modify('+400 days')->format('Y-m-d'), $today->modify('-20 days')->format('Y-m-d'), 1],
 ];
 foreach ($plan as [$barcode, $name, $expiry, $opened, $pao]) {
  $product = db_one('SELECT id FROM products WHERE barcode = ?', [$barcode]);
  [$effective] = effective_expiry($expiry, $opened, $pao);
  db_insert('INSERT INTO user_products (user_id, product_id, custom_name, barcode, expiry_date, opened_date, pao_months, effective_expiry_date, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL, ?)', [
   $id, $product ? (int)$product['id'] : null, $name, $barcode, $expiry, $opened, $pao, $effective, now_iso(),
  ]); } }
