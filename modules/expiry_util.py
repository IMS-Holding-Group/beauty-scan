from datetime import date, timedelta


def add_months(start, months):
 month_index = start.month - 1 + int(months)
 year = start.year + month_index // 12
 month = month_index % 12 + 1
 if month == 12:
  next_month = date(year + 1, 1, 1)
 else:
  next_month = date(year, month + 1, 1)
 last_day = (next_month - timedelta(days=1)).day
 return date(year, month, min(start.day, last_day))


def parse_iso_day(value):
 if not value:
  return None
 year, month, day = str(value)[:10].split("-")
 return date(int(year), int(month), int(day))


def effective_expiry(expiry_iso, opened_iso, pao_months):
 expiry = parse_iso_day(expiry_iso)
 opened = parse_iso_day(opened_iso)
 reason = "expiry"
 effective = expiry
 if opened and pao_months:
  pao_date = add_months(opened, int(pao_months))
  if pao_date < expiry:
   effective = pao_date
   reason = "pao"
 return effective.isoformat(), reason


def status_for(effective_iso, notice_days, today):
 effective = parse_iso_day(effective_iso)
 days = (effective - today).days
 if days < 0:
  return "expired", days
 if days <= 7:
  return "week", days
 if days <= int(notice_days):
  return "soon", days
 return "ok", days


def stages_reached(days, notice_days):
 stages = []
 if days <= int(notice_days):
  stages.append("soon")
 if days <= 7:
  stages.append("week")
 if days < 0:
  stages.append("expired")
 return stages


REASON_TEXT = {
 "expiry": "انتهاء الصلاحية المطبوع",
 "pao": "انتهاء مدة الاستخدام بعد الفتح",
}


if __name__ == "__main__":
 assert add_months(date(2026, 1, 31), 1) == date(2026, 2, 28)
 effective, reason = effective_expiry("2027-01-01", "2026-01-01", 6)
 assert effective == "2026-07-01" and reason == "pao"
 effective, reason = effective_expiry("2026-06-01", "2026-01-01", 12)
 assert effective == "2026-06-01" and reason == "expiry"
 status, days = status_for("2026-10-01", 30, date(2026, 10, 19))
 assert status == "expired"
 status, days = status_for("2026-10-24", 30, date(2026, 10, 19))
 assert status == "week"
 status, days = status_for("2026-11-08", 30, date(2026, 10, 19))
 assert status == "soon"
 status, days = status_for("2027-06-01", 30, date(2026, 10, 19))
 assert status == "ok"
 assert stages_reached(5, 30) == ["soon", "week"]
 assert stages_reached(-1, 30) == ["soon", "week", "expired"]
 assert stages_reached(20, 30) == ["soon"]
 print("expiry ok")
