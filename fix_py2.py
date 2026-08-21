
import glob
import re

files = glob.glob("resources/views/**/*.blade.php", recursive=True)

for filepath in files:
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()
        
    original = content
    
    # We want to match buttons with class containing py-2 that say Filter or Reset
    # Example: <button type="submit" class="btn btn-primary py-2 flex-fill">Filter</button>
    # We will replace py-2 with empty string
    
    # Match py-2 in Filter/Reset/Apply buttons
    def replace_py2(m):
        cls_attr = m.group(1).replace(" py-2", "").replace("py-2 ", "").replace("py-2", "")
        return f'class="{cls_attr}"{m.group(2)}>' + m.group(3) + f'</button>'
        
    content = re.sub(r'class="([^"]*py-2[^"]*)"([^>]*)>((?:<i[^>]*>.*?</i>\s*)?(?:Filter|Reset|Apply)\s*)</button>', replace_py2, content, flags=re.IGNORECASE)
    
    # Match btn-sm in Filter/Reset/Apply buttons
    def replace_btnsm(m):
        cls_attr = m.group(1).replace(" btn-sm", "").replace("btn-sm ", "").replace("btn-sm", "")
        return f'class="{cls_attr}"{m.group(2)}>' + m.group(3) + f'</button>'
        
    content = re.sub(r'class="([^"]*btn-sm[^"]*)"([^>]*)>((?:<i[^>]*>.*?</i>\s*)?(?:Filter|Reset|Apply)\s*)</button>', replace_btnsm, content, flags=re.IGNORECASE)
    
    if content != original:
        with open(filepath, "w", encoding="utf-8") as f:
            f.write(content)
        print(f"Fixed py-2/btn-sm in {filepath}")

