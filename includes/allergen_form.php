<form id="allergen-form">
<label for="allergen-search">بحث في المكونات</label>
<input id="allergen-search" type="search" autocomplete="off">
<ul id="allergen-results" class="pick-list"></ul>
<label for="allergen-manual">كتابة مكوّن</label>
<div class="inline-field">
<input id="allergen-manual" type="text" maxlength="80">
<button type="button" id="allergen-add">إضافة</button>
</div>
<ul id="allergen-selected" class="chip-list"></ul>
<p id="form-message" class="msg" role="alert"></p>
<button type="button" id="allergen-save" data-finish="<?php echo (int)($finish ?? 0); ?>">حفظ</button>
<?php if (!empty($showSkip)) { ?><button type="button" id="skip-onboarding" class="btn-ghost">تخطي</button><?php } ?>
</form>
