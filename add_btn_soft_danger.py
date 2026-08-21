
import re

filepath = "resources/css/app.css"
with open(filepath, "r", encoding="utf-8") as f:
    content = f.read()

if "btn-soft-danger" not in content:
    content += """

/* --- SOFT DANGER BUTTON --- */
.btn-soft-danger {
    color: var(--erp-danger, #ef4444);
    background-color: transparent;
    border: 1px solid var(--erp-gray-300, #cbd5e1);
}
.btn-soft-danger:hover, .btn-soft-danger:focus {
    color: #fff;
    background-color: var(--erp-danger, #ef4444);
    border-color: var(--erp-danger, #ef4444);
}
"""
    with open(filepath, "w", encoding="utf-8") as f:
        f.write(content)
    print("CSS injected")
else:
    print("CSS already injected")

