# -*- coding: utf-8 -*-
"""
اسپرایت آیکون‌ها و اسکلت اپلیکیشن را داخل هر شش صفحه تزریق می‌کند.
اجرای دوباره‌اش بی‌خطر است — بلاک قبلی جایگزین می‌شود.
"""
import io, os, re, glob

BASE = os.path.dirname(os.path.abspath(__file__))

def read(p):  return io.open(p, encoding="utf-8").read()
def write(p, s): io.open(p, "w", encoding="utf-8").write(s)

BLOCKS = [
    ("ICONS",    "<!--ICONS-->"    + read(os.path.join(BASE, "assets", "icons.html")).strip()    + "<!--/ICONS-->"),
    ("APPSHELL", "<!--APPSHELL-->" + read(os.path.join(BASE, "assets", "appshell.html")).strip() + "<!--/APPSHELL-->"),
]

for path in sorted(glob.glob(os.path.join(BASE, "*.html"))):
    html = new = read(path)
    for name, block in BLOCKS:
        open_tag, close_tag = "<!--%s-->" % name, "<!--/%s-->" % name
        if close_tag in new:
            new = re.sub(re.escape(open_tag) + ".*?" + re.escape(close_tag),
                         lambda m: block, new, flags=re.S)
        elif open_tag in new:
            new = new.replace(open_tag, block)
        else:                                     # marker lost — restore after <body>
            new = new.replace("<body", "<!--%s-PLACEHOLDER-->\n<body" % name, 1)
            new = new.replace("<!--%s-PLACEHOLDER-->\n" % name, "")
            new = re.sub(r"(<body[^>]*>)", lambda m: m.group(1) + "\n" + block, new, count=1)
    if new != html:
        write(path, new)
        print("built:", os.path.basename(path))
