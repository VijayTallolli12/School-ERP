
import glob
import re

files = glob.glob("app/**/*.php", recursive=True) + glob.glob("resources/**/*.php", recursive=True)

count = 0
for filepath in files:
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()

    # Find buttons that contain ti-dots-vertical but DO NOT have action-menu-trigger
    # Exclude the component file itself since we just modified it.
    if "table-action-menu.blade.php" in filepath:
        continue
        
    matches = re.finditer(r'(<button[^>]*>.*?ti-dots-vertical.*?</button>)', content, re.IGNORECASE | re.DOTALL)
    for m in matches:
        if "action-menu-trigger" not in m.group(1):
            count += 1
            print(f"Found in {filepath}: {m.group(1)}")

