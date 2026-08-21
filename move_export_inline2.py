
import glob
import re

files = glob.glob("resources/views/modules/reports/**/*.blade.php", recursive=True)

for filepath in files:
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()

    original = content
    
    # We want to find the <x-erp.export-buttons /> component block
    m_export = re.search(r'(?:<!--\s*Export Buttons\s*-->\s*)?(?:<div[^>]*>\s*)?(<x-erp\.export-buttons(?:(?!/>).)*/>)\s*(?:</div>\s*)?', content, re.DOTALL)
    if not m_export:
        continue
        
    export_html = m_export.group(1)
    
    # Check if it is already inside the form or alongside filterBtn!
    # If it is inside a col-12 d-flex gap-2, it might already be inline.
    
    # We will just remove it.
    content = content.replace(m_export.group(0), "")
    
    # Find the filter button
    # Usually it is <button type="button" id="filterBtn"...>Filter</button>
    # Or <button type="submit"...>Filter</button>
    
    # Let us inject it before </form> or inside the button group
    # A safe pattern: find Reset button and insert after it
    
    m_reset = re.search(r'(</button>\s*)(</div>\s*</form>)', content)
    if m_reset:
        content = content.replace(m_reset.group(0), m_reset.group(1) + export_html + "\n" + m_reset.group(2))
    else:
        m_reset2 = re.search(r'(</button>\s*</form>)', content)
        if m_reset2:
            content = content.replace(m_reset2.group(0), export_html + "\n" + m_reset2.group(0))
        else:
            m_reset3 = re.search(r'(</button>\s*</div>\s*</div>\s*</form>)', content)
            if m_reset3:
                content = content.replace(m_reset3.group(0), export_html + "\n" + m_reset3.group(0))
            else:
                pass
                
    if content != original:
        with open(filepath, "w", encoding="utf-8") as f:
            f.write(content)
        print(f"Moved inline in {filepath}")

