
import os
import re

directories = [
    "resources/views/modules"
]

for root, _, files in os.walk("resources/views/modules"):
    for file in files:
        if file.endswith(".blade.php"):
            filepath = os.path.join(root, file)
            with open(filepath, "r", encoding="utf-8") as f:
                content = f.read()
            
            # Find filter buttons (Apply or Filter) with btn-sm
            original = content
            
            # 1. Replace id="btnApplyFilters" class="btn btn-primary btn-sm w-100" -> btn-primary w-100
            content = re.sub(r'class="([^"]*)btn-sm([^"]*)"([^>]*)>(Apply|Filter)</button>', r'class="\1\2"\3>\4</button>', content)
            
            if content != original:
                with open(filepath, "w", encoding="utf-8") as f:
                    f.write(content)
                print(f"Fixed button in {filepath}")

