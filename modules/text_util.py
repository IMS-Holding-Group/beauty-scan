def normalize_text(value):
 chars = []
 previous_space = False
 for char in str(value or "").lower():
  if char.isalnum() or "\u0600" <= char <= "\u06FF":
   chars.append(char)
   previous_space = False
  elif char.isspace():
   if chars and not previous_space:
    chars.append(" ")
    previous_space = True
 text = "".join(chars).strip()
 return " ".join(text.split())


def normalize_with_map(value):
 chars = []
 mapping = []
 previous_space = False
 for index, char in enumerate(str(value or "")):
  if char.isalnum() or "\u0600" <= char <= "\u06FF":
   chars.append(char.lower())
   mapping.append(index)
   previous_space = False
  elif char.isspace():
   if chars and not previous_space:
    chars.append(" ")
    mapping.append(index)
    previous_space = True
 while chars and chars[0] == " ":
  chars.pop(0)
  mapping.pop(0)
 while chars and chars[-1] == " ":
  chars.pop()
  mapping.pop()
 return "".join(chars), mapping


def original_slice(segment, needle):
 norm, mapping = normalize_with_map(segment)
 index = norm.find(needle)
 if index < 0 or not mapping:
  return str(segment or "").strip()
 end_index = index + len(needle) - 1
 if end_index >= len(mapping):
  return str(segment or "").strip()
 start = mapping[index]
 end = mapping[end_index] + 1
 return str(segment)[start:end].strip()


if __name__ == "__main__":
 assert normalize_text("  Parfum (Fragrance) ") == "parfum fragrance"
 assert normalize_text("حمض،   الهيالورونيك") == "حمض الهيالورونيك"
 assert original_slice("Parfum (Fragrance)", "parfum") == "Parfum"
 assert original_slice("Parfum (Fragrance)", "fragrance") == "Fragrance"
 print("text ok")
