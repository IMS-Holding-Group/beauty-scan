from datetime import datetime, timedelta, timezone

RIYADH = timezone(timedelta(hours=3))


def now_utc():
 return datetime.now(timezone.utc)


def now_iso():
 return now_utc().replace(microsecond=0).isoformat()


def today_riyadh():
 return now_utc().astimezone(RIYADH).date()


def _as_utc(value):
 text = str(value)
 if text.endswith("Z"):
  text = text[:-1] + "+00:00"
 parsed = datetime.fromisoformat(text)
 if parsed.tzinfo is None:
  parsed = parsed.replace(tzinfo=timezone.utc)
 return parsed.astimezone(timezone.utc)


def date_parts(iso_day):
 year, month, day = str(iso_day)[:10].split("-")
 return {"y": int(year), "m": int(month), "d": int(day)}


def datetime_parts(iso_dt):
 local = _as_utc(iso_dt).astimezone(RIYADH)
 hour = local.hour
 suffix = "ص" if hour < 12 else "م"
 hour12 = hour % 12
 if hour12 == 0:
  hour12 = 12
 return {
  "y": local.year,
  "m": local.month,
  "d": local.day,
  "h": hour12,
  "min": f"{local.minute:02d}",
  "suffix": suffix,
 }


def plain_date(iso_day):
 parts = date_parts(iso_day)
 return f"{parts['y']}/{parts['m']}/{parts['d']}م"


def plain_datetime(iso_dt):
 parts = datetime_parts(iso_dt)
 return f"{parts['y']}/{parts['m']}/{parts['d']}م {parts['h']}:{parts['min']}{parts['suffix']}"


if __name__ == "__main__":
 assert plain_date("2026-01-03") == "2026/1/3م"
 assert date_parts("2026-01-03")["m"] == 1
 morning = datetime_parts("2026-01-03T02:50:00+00:00")
 assert morning["h"] == 5 and morning["min"] == "50" and morning["suffix"] == "ص"
 evening = datetime_parts("2026-01-03T15:05:00+00:00")
 assert evening["h"] == 6 and evening["suffix"] == "م"
 midnight = datetime_parts("2026-01-02T21:00:00+00:00")
 assert midnight["h"] == 12 and midnight["suffix"] == "ص" and midnight["d"] == 3
 print("dates ok")
