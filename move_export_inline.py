
import glob
import re

files = glob.glob("resources/views/modules/reports/**/*.blade.php", recursive=True)

for filepath in files:
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()

    original = content
    
    # We want to find the <x-erp.export-buttons /> component block
    m_export = re.search(r'\s*(?:<!--.*?-->\s*)?(?:<div[^>]*>\s*)?(<x-erp\.export-buttons[^>]*/>)\s*(?:</div>\s*)?', content)
    if not m_export:
        continue
        
    export_html = m_export.group(1)
    # Remove it from its current location
    content = content.replace(m_export.group(0), "")
    
    # We want to insert export_html right after the Filter/Reset/Apply/Generate button group.
    # Usually this is before </form> or inside the last <div ...> of the form.
    # Let us just look for `</button>` of a Filter/Reset button that is immediately followed by `</div>` or `</form>`.
    
    # Pattern to find the last button in the form
    m_form = re.search(r'(</button>\s*</div>\s*</div>\s*)</form>', content)
    if m_form:
        content = content.replace(m_form.group(1), f"\n{export_html}\n" + m_form.group(1))
    else:
        m_form2 = re.search(r'(</button>\s*</div>\s*)</form>', content)
        if m_form2:
            content = content.replace(m_form2.group(1), f"\n{export_html}\n" + m_form2.group(1))
        else:
            m_form3 = re.search(r'(</button>\s*)</form>', content)
            if m_form3:
                content = content.replace(m_form3.group(1), f"\n{export_html}\n" + m_form3.group(1))
            else:
                # directory.blade.php already has it inside the div! Wait, my regex might match and remove it, then put it back.
                # Let us check if it is already inside the form.
                pass

    if content != original:
        with open(filepath, "w", encoding="utf-8") as f:
            f.write(content)
        print(f"Moved inline in {filepath}")

