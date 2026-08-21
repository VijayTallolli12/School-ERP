
import re

filepath = "resources/views/modules/parents/index.blade.php"
with open(filepath, "r", encoding="utf-8") as f:
    content = f.read()

old_str1 = """                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-student">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>"""

new_str1 = """                            <div class="col-auto">
                                <label class="form-label d-none d-md-block">&nbsp;</label>
                                <button type="button" class="btn btn-soft-danger remove-student p-0 d-flex align-items-center justify-content-center" title="Remove Student" style="width: 43px; height: 43px;">
                                    <i class="ti ti-trash fs-5"></i>
                                </button>
                            </div>"""

old_str2 = """                                            <div class="col-md-2">
                                                <label class="form-label">&nbsp;</label>
                                                <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-student">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </div>"""

new_str2 = """                                            <div class="col-auto">
                                                <label class="form-label d-none d-md-block">&nbsp;</label>
                                                <button type="button" class="btn btn-soft-danger remove-student p-0 d-flex align-items-center justify-content-center" title="Remove Student" style="width: 43px; height: 43px;">
                                                    <i class="ti ti-trash fs-5"></i>
                                                </button>
                                            </div>"""

content = content.replace(old_str1, new_str1)
content = content.replace(old_str2, new_str2)

with open(filepath, "w", encoding="utf-8") as f:
    f.write(content)
print("Updated parents view")

