
import glob
import re

files = glob.glob("resources/views/**/*.blade.php", recursive=True)

for filepath in files:
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()
        
    original = content
    
    # Match py-2 in Filter/Reset/Apply buttons
    def replace_py2(m):
        cls_attr = m.group(1).replace(" py-2", "").replace("py-2 ", "").replace("py-2", "")
        return f'class="{cls_attr}"{m.group(2)}>' + m.group(3) + f'</button>'
        
    content = re.sub(r'class="([^"]*py-2[^"]*)"([^>]*)>(\s*(?:<i[^>]*>.*?</i>\s*)?(?:Filter|Reset|Apply)\s*)</button>', replace_py2, content, flags=re.IGNORECASE | re.DOTALL)
    
    # Match btn-sm in Filter/Reset/Apply buttons
    def replace_btnsm(m):
        cls_attr = m.group(1).replace(" btn-sm", "").replace("btn-sm ", "").replace("btn-sm", "")
        return f'class="{cls_attr}"{m.group(2)}>' + m.group(3) + f'</button>'
        
    content = re.sub(r'class="([^"]*btn-sm[^"]*)"([^>]*)>(\s*(?:<i[^>]*>.*?</i>\s*)?(?:Filter|Reset|Apply)\s*)</button>', replace_btnsm, content, flags=re.IGNORECASE | re.DOTALL)
    
    if content != original:
        with open(filepath, "w", encoding="utf-8") as f:
            f.write(content)
        print(f"Fixed py-2/btn-sm in {filepath}")

