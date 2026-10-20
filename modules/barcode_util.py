def check_digit(data, left_odd_weight):
 # الوزن 1 لـ EAN من اليسار، والوزن 3 لـ UPC-A من اليسار
 other = 1 if left_odd_weight == 3 else 3
 total = 0
 for index, char in enumerate(data):
  number = int(char)
  total += number * (left_odd_weight if index % 2 == 0 else other)
 return (10 - (total % 10)) % 10


def make_ean13(twelve):
 return twelve + str(check_digit(twelve, 1))


def make_ean8(seven):
 return seven + str(check_digit(seven, 1))


def make_upca(eleven):
 return eleven + str(check_digit(eleven, 3))


def valid_ean13(code):
 return len(code) == 13 and code.isdigit() and int(code[-1]) == check_digit(code[:-1], 1)


def valid_ean8(code):
 return len(code) == 8 and code.isdigit() and int(code[-1]) == check_digit(code[:-1], 1)


def valid_upca(code):
 return len(code) == 12 and code.isdigit() and int(code[-1]) == check_digit(code[:-1], 3)


def expand_upce(code):
 if len(code) != 8 or not code.isdigit() or code[0] not in "01":
  return ""
 digits = code[1:7]
 last = digits[5]
 if last in "012":
  maker = digits[0] + digits[1] + last
  product = "0000" + digits[2] + digits[3] + digits[4]
 elif last == "3":
  maker = digits[0:3]
  product = "00000" + digits[3] + digits[4]
 elif last == "4":
  maker = digits[0:4]
  product = "00000" + digits[4]
 else:
  maker = digits[0:5]
  product = "0000" + last
 return code[0] + maker + product + code[7]


def valid_upce(code):
 expanded = expand_upce(code)
 return bool(expanded) and valid_upca(expanded)


def clean_barcode(raw):
 text = str(raw or "").strip()
 digits = []
 for char in text:
  if char in " -":
   continue
  if not char.isdigit():
   return ""
  digits.append(char)
 return "".join(digits)


def is_valid_barcode(code):
 if not code or not code.isdigit():
  return False
 if len(code) == 13:
  return valid_ean13(code)
 if len(code) == 12:
  return valid_upca(code)
 if len(code) == 8:
  return valid_ean8(code) or valid_upce(code)
 return False


if __name__ == "__main__":
 sample13 = make_ean13("628100000001")
 assert valid_ean13(sample13)
 assert not valid_ean13(sample13[:-1] + str((int(sample13[-1]) + 1) % 10))
 sample8 = make_ean8("9638507")
 assert sample8 == "96385074"
 assert valid_ean8(sample8)
 sample12 = make_upca("12345678901")
 assert valid_upca(sample12)
 body = "01210000345"
 upce = "0" + "123451" + str(check_digit(body, 3))
 assert valid_upce(upce)
 assert expand_upce(upce) == body + upce[-1]
 assert clean_barcode(" 6281-0000-0001 ") == "628100000001"
 assert clean_barcode("12A45") == ""
 assert is_valid_barcode("123") is False
 print("barcode ok")
