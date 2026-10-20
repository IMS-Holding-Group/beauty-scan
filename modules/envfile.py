import os


def load_env(path):
 # قارئ بسيط لملف البيئة دون مكتبة إضافية
 if not os.path.isfile(path):
  return
 with open(path, "r", encoding="utf-8") as handle:
  for raw in handle:
   line = raw.strip()
   if not line or line.startswith("#") or "=" not in line:
    continue
   key, value = line.split("=", 1)
   key = key.strip()
   value = value.strip()
   if len(value) >= 2 and value[0] == value[-1] and value[0] in "\"'":
    value = value[1:-1]
   if key and key not in os.environ:
    os.environ[key] = value


def env(key, default=""):
 return os.environ.get(key, default).strip()
