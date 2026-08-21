
import re

filepath = "resources/css/app.css"
with open(filepath, "r", encoding="utf-8") as f:
    content = f.read()

# Find where .form-control is defined
m = re.search(r'\.form-control,\s*\.form-select\s*{[^}]*}', content)

if m:
    injection = """

/* --- FILTER BUTTONS SYNC --- */
/* Ensure buttons next to form controls exactly match their custom 43px height */
.filter-item .btn,
#filterForm .btn,
form.row .btn,
.d-flex.align-items-end .btn {
    padding: 10px 16px !important;
    min-height: 43px !important;
}
"""
    new_content = content[:m.end()] + injection + content[m.end():]
    with open(filepath, "w", encoding="utf-8") as f:
        f.write(new_content)
    print("CSS injected")
else:
    print("Could not find .form-control")

