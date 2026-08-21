
import glob
import re

files = glob.glob("app/**/*.php", recursive=True) + glob.glob("resources/**/*.php", recursive=True)

for filepath in files:
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()

    # Find ANY tag that has dropdown-toggle and contains ti-dots-vertical
    # This might be too broad if the button contains other things, but let's just see.
    matches = re.finditer(r'(<(?:button|a)[^>]*dropdown-toggle[^>]*>.*?ti-dots-vertical.*?</(?:button|a)>)', content, re.IGNORECASE | re.DOTALL)
    for m in matches:
        # Check if the tag is short enough to just be a button
        if len(m.group(1)) < 200:
            print(f"Found in {filepath}: {m.group(1)}")

