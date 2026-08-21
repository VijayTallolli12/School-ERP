
import glob
import re

files = glob.glob("resources/views/modules/**/*.blade.php", recursive=True)

for filepath in files:
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()
        
    original = content
    
    # Remove py-2 from elements that have btn and py-2
    def remove_py2(m):
        cls_attr = m.group(1).replace(" py-2", "").replace("py-2 ", "").replace("py-2", "")
        return f'class="{cls_attr}"'
        
    content = re.sub(r'class="([^"]*\bbtn\b[^"]*\bpy-2\b[^"]*)"', remove_py2, content)
    
    if content != original:
        with open(filepath, "w", encoding="utf-8") as f:
            f.write(content)
        print(f"Removed py-2 in {filepath}")

