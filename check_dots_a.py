
import glob
import re

files = glob.glob("app/**/*.php", recursive=True) + glob.glob("resources/**/*.php", recursive=True)

count = 0
for filepath in files:
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()

    matches = re.finditer(r'(<a[^>]*data-bs-toggle="dropdown"[^>]*>.*?ti-dots-vertical.*?</a>)', content, re.IGNORECASE | re.DOTALL)
    for m in matches:
        if "action-menu-trigger" not in m.group(1):
            count += 1
            print(f"Found in {filepath}: {m.group(1)}")

