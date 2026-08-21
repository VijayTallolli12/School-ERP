
import re

with open("resources/views/modules/reports/students/index.blade.php", "r", encoding="utf-8") as f:
    content = f.read()

m_export = re.search(r'(<x-erp\.export-buttons(?:(?!/>).)*/>)', content, re.DOTALL)
if m_export:
    print("Found export block!")
else:
    print("Did not find export block")

