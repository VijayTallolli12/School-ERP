
import re

filepath = "resources/css/app.css"
with open(filepath, "r", encoding="utf-8") as f:
    content = f.read()

if "action-menu-trigger" not in content:
    content += """

/* --- ACTION MENU TRIGGER --- */
/* Hide Bootstrap caret from three-dots action menus globally */
.action-menu-trigger::after,
.action-menu-trigger.dropdown-toggle::after {
    display: none !important;
}
"""
    with open(filepath, "w", encoding="utf-8") as f:
        f.write(content)
    print("CSS injected")
else:
    print("CSS already injected")

