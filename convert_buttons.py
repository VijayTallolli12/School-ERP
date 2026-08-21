
import os
import re

files = [
    "resources/views/modules/reports/absent_students/index.blade.php",
    "resources/views/modules/reports/attendance/class_wise.blade.php",
    "resources/views/modules/reports/attendance/daily.blade.php",
    "resources/views/modules/reports/attendance/monthly.blade.php",
    "resources/views/modules/reports/students/admission.blade.php",
    "resources/views/modules/reports/students/class_wise.blade.php",
    "resources/views/modules/reports/students/directory.blade.php",
    "resources/views/modules/reports/students/gender_wise.blade.php",
    "resources/views/modules/reports/students/index.blade.php",
    "resources/views/modules/reports/teachers/workload.blade.php"
]

for file_path in files:
    if not os.path.exists(file_path):
        print(f"Not found: {file_path}")
        continue
        
    with open(file_path, "r", encoding="utf-8") as f:
        content = f.read()
        
    # We need to find the <a ...>...Export Excel</a>... PDF ... Print pattern and replace it
    # Since regex is hard for this, we will find "Export Excel" or "Excel" and look around.
    
    # Actually, I can use a simpler approach. I will just do it manually for each file if there are only 10, or write a robust script.
    print(f"File: {file_path} length: {len(content)}")

