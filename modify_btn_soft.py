
import re

filepath = "resources/css/app.css"
with open(filepath, "r", encoding="utf-8") as f:
    content = f.read()

content = content.replace("border: 1px solid var(--erp-gray-300, #cbd5e1);", "border: 1px solid rgba(220, 38, 38, 0.3);")

with open(filepath, "w", encoding="utf-8") as f:
    f.write(content)
print("Updated border")

