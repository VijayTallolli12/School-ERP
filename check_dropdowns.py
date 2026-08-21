
import glob
import re

files = glob.glob("app/**/*.php", recursive=True) + glob.glob("resources/**/*.php", recursive=True)

count = 0
for filepath in files:
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()

    # Find buttons that have both dropdown-toggle and ti-dots-vertical
    # They can be on the same line or next line.
    matches = re.finditer(r'(<button[^>]*class="[^"]*dropdown-toggle[^"]*"[^>]*data-bs-toggle="dropdown"[^>]*>\s*<i[^>]*class="[^"]*ti-dots-vertical[^"]*"[^>]*></i>\s*</button>)', content, re.IGNORECASE | re.DOTALL)
    for m in matches:
        count += 1
        print(f"Found in {filepath}: {m.group(1)}")

