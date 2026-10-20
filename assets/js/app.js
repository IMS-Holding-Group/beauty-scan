const navToggle = document.getElementById("nav-toggle");
const siteNav = document.getElementById("site-nav");
if (navToggle && siteNav) navToggle.addEventListener("click", () => {
 const open = siteNav.classList.toggle("is-open");
 navToggle.setAttribute("aria-expanded", open ? "true" : "false");
});

function csrf() {
 const meta = document.querySelector('meta[name="csrf-token"]');
 return meta ? meta.getAttribute("content") || "" : "";
}

async function api(action, payload, method) {
 const verb = method || "POST";
 const headers = {"X-CSRF-Token": csrf(), "Accept": "application/json"};
 const options = {method: verb, headers};
 if (verb !== "GET") {
  headers["Content-Type"] = "application/json";
  options.body = JSON.stringify(payload || {}); }
 let response;
 try { response = await fetch("/api.php?action=" + encodeURIComponent(action), options); }
 catch (error) { return {ok: false, message: "تعذر الاتصال. حاول مرة أخرى."}; }
 try { return await response.json(); }
 catch (error) { return {ok: false, message: "تعذر تنفيذ الطلب"}; }
}

function formValues(form) {
 const data = {};
 new FormData(form).forEach((value, key) => { data[key] = String(value); });
 return data;
}

function showMessage(id, text) {
 const el = document.getElementById(id);
 if (!el) { return; }
 el.textContent = text || "";
}

async function submitAction(form, action, messageId) {
 const button = form.querySelector("button[type='submit']");
 const old = button ? button.textContent : "";
 if (button) {
  button.disabled = true;
  button.textContent = "جارٍ التنفيذ"; }
 const data = await api(action, formValues(form));
 if (data.redirect) { window.location.href = data.redirect; return; }
 if (button) {
  button.disabled = false;
  button.textContent = old; }
 showMessage(messageId, data.ok ? (data.message || "تم الحفظ") : (data.message || "تعذر تنفيذ الطلب"));
}

function formatDate(parts) {
 return String(parts.y) + "/" + String(parts.m) + "/" + String(parts.d) + "م";
}

function formatDateTime(parts) {
 return formatDate(parts) + " " + String(parts.h) + ":" + String(parts.min) + String(parts.suffix);
}

function checkDigit(data, leftOddWeight) {
 const other = leftOddWeight === 3 ? 1 : 3;
 let total = 0;
 for (let i = 0; i < data.length; i += 1) {
  const number = Number(data[i]);
  total += number * (i % 2 === 0 ? leftOddWeight : other); }
 return (10 - (total % 10)) % 10;
}

function validEan13(code) {
 return code.length === 13 && Number(code[12]) === checkDigit(code.slice(0, 12), 1);
}

function validEan8(code) {
 return code.length === 8 && Number(code[7]) === checkDigit(code.slice(0, 7), 1);
}

function validUpca(code) {
 return code.length === 12 && Number(code[11]) === checkDigit(code.slice(0, 11), 3);
}

function expandUpce(code) {
 if (code.length !== 8 || (code[0] !== "0" && code[0] !== "1")) { return ""; }
 const digits = code.slice(1, 7);
 const last = digits[5];
 let maker = "";
 let product = "";
 if (last === "0" || last === "1" || last === "2") {
  maker = digits[0] + digits[1] + last;
  product = "0000" + digits[2] + digits[3] + digits[4]; }
 else if (last === "3") {
  maker = digits.slice(0, 3);
  product = "00000" + digits[3] + digits[4]; }
 else if (last === "4") {
  maker = digits.slice(0, 4);
  product = "00000" + digits[4]; }
 else {
  maker = digits.slice(0, 5);
  product = "0000" + last; }
 return code[0] + maker + product + code[7];
}

function validUpce(code) {
 const expanded = expandUpce(code);
 return expanded.length === 12 && validUpca(expanded);
}

function isValidBarcode(code) {
 if (!/^\d+$/.test(code)) { return false; }
 if (code.length === 13) { return validEan13(code); }
 if (code.length === 12) { return validUpca(code); }
 if (code.length === 8) { return validEan8(code) || validUpce(code); }
 return false;
}

function cleanBarcode(raw) {
 let digits = "";
 for (const char of String(raw || "").trim()) {
  if (char === " " || char === "-") { continue; }
  if (!/\d/.test(char)) { return ""; }
  digits += char; }
 return digits;
}

const authForm = document.getElementById("auth-form");
if (authForm) authForm.addEventListener("submit", (event) => {
 event.preventDefault();
 submitAction(authForm, authForm.dataset.action, "form-message");
});

const productForm = document.getElementById("product-form");
if (productForm) productForm.addEventListener("submit", (event) => {
 event.preventDefault();
 submitAction(productForm, "product_save", "form-message");
});

const accountForm = document.getElementById("account-form");
if (accountForm) accountForm.addEventListener("submit", (event) => {
 event.preventDefault();
 submitAction(accountForm, "account", "form-message");
});

const passwordForm = document.getElementById("password-form");
if (passwordForm) passwordForm.addEventListener("submit", (event) => {
 event.preventDefault();
 submitAction(passwordForm, "password", "password-message");
});

const logoutButton = document.getElementById("logout-button");
if (logoutButton) logoutButton.addEventListener("click", async () => {
 const data = await api("logout", {});
 window.location.href = data.redirect || "/";
});

document.querySelectorAll("[data-toggle='password']").forEach((button) => {
 button.addEventListener("click", () => {
  const input = button.parentElement.querySelector("input");
  if (!input) { return; }
  const show = input.type === "password";
  input.type = show ? "text" : "password";
  const label = button.querySelector("span");
  const icon = button.querySelector("i");
  if (label) { label.textContent = show ? "إخفاء" : "إظهار"; }
  if (icon) { icon.className = show ? "fa-solid fa-eye-slash" : "fa-solid fa-eye"; } });
});

const passwordInput = document.getElementById("password");
const strength = document.getElementById("strength");
if (passwordInput && strength && document.body.dataset.page === "register") {
 passwordInput.addEventListener("input", () => {
  const value = passwordInput.value;
  let label = "قصيرة";
  if (value.length >= 8 && /[A-Za-z]/.test(value) && /\d/.test(value)) { label = "جيدة"; }
  else if (value.length >= 8) { label = "مقبولة"; }
  strength.textContent = label; });
}

document.querySelectorAll("[data-delete-product]").forEach((button) => {
 button.addEventListener("click", async () => {
  if (!window.confirm("حذف المنتج من حسابك؟")) { return; }
  const data = await api("product_delete", {id: Number(button.dataset.deleteProduct)});
  if (data.ok) { window.location.reload(); }
  else { window.alert(data.message || "تعذر الحذف"); } });
});

document.querySelectorAll("[data-delete-allergen]").forEach((button) => {
 button.addEventListener("click", async () => {
  const data = await api("allergens_delete", {id: Number(button.dataset.deleteAllergen)});
  if (data.ok) { window.location.reload(); } });
});

const selected = [];

function drawSelected() {
 const list = document.getElementById("allergen-selected");
 if (!list) { return; }
 list.replaceChildren();
 selected.forEach((name, index) => {
  const item = document.createElement("li");
  const button = document.createElement("button");
  button.type = "button";
  button.textContent = name;
  button.addEventListener("click", () => {
   selected.splice(index, 1);
   drawSelected(); });
  item.appendChild(button);
  list.appendChild(item); });
}

function addSelected(name) {
 const clean = name.trim();
 if (clean.length < 2 || selected.includes(clean)) { return; }
 selected.push(clean);
 drawSelected();
}

const search = document.getElementById("allergen-search");
if (search) {
 let catalog = [];
 api("ingredients", null, "GET").then((data) => { catalog = data.items || []; });
 const results = document.getElementById("allergen-results");
 search.addEventListener("input", () => {
  if (!results) { return; }
  results.replaceChildren();
  const query = search.value.trim().toLowerCase();
  if (query.length < 1) { return; }
  catalog.filter((item) => (item.inci_name + " " + item.arabic_name + " " + item.synonyms).toLowerCase().includes(query)).slice(0, 8).forEach((item) => {
   const li = document.createElement("li");
   const button = document.createElement("button");
   button.type = "button";
   button.textContent = item.inci_name + " - " + item.arabic_name;
   button.addEventListener("click", () => {
    addSelected(item.inci_name);
    search.value = "";
    results.replaceChildren(); });
   li.appendChild(button);
   results.appendChild(li); }); });
 const addButton = document.getElementById("allergen-add");
 const manual = document.getElementById("allergen-manual");
 if (addButton && manual) addButton.addEventListener("click", () => {
  addSelected(manual.value);
  manual.value = "";
 });
 const save = document.getElementById("allergen-save");
 if (save) save.addEventListener("click", async () => {
  if (manual && manual.value.trim() !== "") { addSelected(manual.value); manual.value = ""; }
  const data = await api("allergens_save", {names: selected, finish: save.dataset.finish === "1" ? 1 : 0});
  if (data.redirect) { window.location.href = data.redirect; return; }
  showMessage("form-message", data.message || "تعذر الحفظ");
  if (data.ok) { window.location.reload(); } });
 const skip = document.getElementById("skip-onboarding");
 if (skip) skip.addEventListener("click", async () => {
  const data = await api("onboarding_skip", {});
  if (data.redirect) { window.location.href = data.redirect; } });
}

let cameraStream = null;

function stopCamera() {
 if (!cameraStream) { return; }
 cameraStream.getTracks().forEach((track) => track.stop());
 cameraStream = null;
}

function showScanReason(text) {
 const el = document.getElementById("manual-reason");
 if (el) { el.textContent = text; }
}

async function submitBarcode(raw) {
 const code = cleanBarcode(raw);
 const message = document.getElementById("form-message");
 if (!code) { if (message) { message.textContent = "أدخل أرقاماً فقط"; } return; }
 if (![8, 12, 13].includes(code.length)) { if (message) { message.textContent = "طول الباركود غير مدعوم. الصيغ المدعومة هي EAN-13 وEAN-8 وUPC-A وUPC-E"; } return; }
 if (!isValidBarcode(code) && message) { message.textContent = "رقم المراقبة غير صحيح، وسيُعرض سبب الحكم في النتيجة."; }
 const data = await api("lookup", {barcode: code});
 if (data.redirect) { window.location.href = data.redirect; return; }
 if (message) { message.textContent = data.message || "تعذر البحث"; }
}

async function startCamera() {
 const video = document.getElementById("camera");
 if (!video) { return; }
 if (!("BarcodeDetector" in window)) { showScanReason("المتصفح لا يدعم قراءة الباركود. أدخل الرقم يدوياً."); return; }
 if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) { showScanReason("الكاميرا غير متاحة في هذا المتصفح. أدخل الرقم يدوياً."); return; }
 try { cameraStream = await navigator.mediaDevices.getUserMedia({video: {facingMode: "environment"}, audio: false}); }
 catch (error) { showScanReason("تعذر استخدام الكاميرا أو رُفض الإذن. أدخل الرقم يدوياً."); return; }
 video.srcObject = cameraStream;
 video.classList.add("is-on");
 try { await video.play(); }
 catch (error) { showScanReason("تعذر استخدام الكاميرا أو رُفض الإذن. أدخل الرقم يدوياً."); return; }
 let detector;
 try { detector = new BarcodeDetector({formats: ["ean_13", "ean_8", "upc_a", "upc_e"]}); }
 catch (error) { showScanReason("المتصفح لا يدعم قراءة الباركود. أدخل الرقم يدوياً."); return; }
 const timer = window.setInterval(async () => {
  try {
   const codes = await detector.detect(video);
   if (!codes.length) { return; }
   window.clearInterval(timer);
   stopCamera();
   video.classList.remove("is-on");
   submitBarcode(codes[0].rawValue); }
  catch (error) {
   window.clearInterval(timer);
   stopCamera();
   video.classList.remove("is-on");
   showScanReason("تعذرت قراءة الباركود من الكاميرا. أدخل الرقم يدوياً."); } }, 500);
}

const manualForm = document.getElementById("manual-form");
if (manualForm) {
 manualForm.addEventListener("submit", (event) => {
  event.preventDefault();
  const input = document.getElementById("barcode-input");
  submitBarcode(input ? input.value : ""); });
 if (document.body.dataset.page === "scan") { startCamera(); window.addEventListener("pagehide", stopCamera); }
}

const seenNotes = new Set();
let notesPrimed = false;

async function pollNotes() {
 const data = await api("unread", null, "GET");
 const count = document.getElementById("unread-count");
 if (count && typeof data.unread === "number") { count.textContent = String(data.unread); }
 const items = data.items || [];
 if (!notesPrimed) {
  items.forEach((item) => seenNotes.add(item.id));
  notesPrimed = true;
  return; }
 items.forEach((item) => {
  if (seenNotes.has(item.id)) { return; }
  seenNotes.add(item.id);
  if (window.Notification && Notification.permission === "granted") {
   const when = item.parts ? " " + formatDate(item.parts) : "";
   new Notification("تنبيه صلاحية", {body: item.lead + when}); } });
}

document.querySelectorAll("[data-verify]").forEach((button) => button.addEventListener("click", async () => {
 button.disabled = true;
 const data = await api("admin_verify", {id: Number(button.dataset.verify), is_verified: Number(button.dataset.flag)});
 if (data.ok) {
  window.location.reload();
  return;
 }
 button.disabled = false;
 showMessage("admin-message", data.message || "تعذر الحفظ");
}));

if (document.getElementById("unread-count") && document.body.dataset.page !== "notifications") {
 const tick = async () => {
  const data = await api("unread", null, "GET");
  const count = document.getElementById("unread-count");
  if (count && typeof data.unread === "number") { count.textContent = String(data.unread); }
 };
 tick();
 window.setInterval(tick, 60000);
}

if (document.body.dataset.page === "notifications") {
 pollNotes();
 window.setInterval(pollNotes, 60000);
 const permit = document.getElementById("notify-permission");
 if (permit) permit.addEventListener("click", () => { if (window.Notification) { Notification.requestPermission(); } });
 const readAll = document.getElementById("read-all");
 if (readAll) readAll.addEventListener("click", async () => {
  const data = await api("notifications_read_all", {});
  if (data.ok) { window.location.reload(); } });
 document.querySelectorAll("[data-read]").forEach((button) => {
  button.addEventListener("click", async () => {
   const data = await api("notification_read", {id: Number(button.dataset.read)});
   if (data.ok) { window.location.reload(); } }); });
}
