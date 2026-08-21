
import glob
import re

files = glob.glob("resources/views/modules/reports/**/*.blade.php", recursive=True)

for filepath in files:
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()

    original = content
    
    # 1. Extract the export HTML component
    m_export = re.search(r'(?:<!--\s*Export Buttons\s*-->\s*)?(?:<div[^>]*>\s*)?(<x-erp\.export-buttons(?:(?!/>).)*/>)\s*(?:</div>\s*)?', content, re.DOTALL)
    if not m_export:
        continue
        
    export_html = m_export.group(1)
    
    # 2. Check if it is already correctly placed next to resetBtn or filterBtn
    # Since I just ran fix_exports2, it is currently in its original place (usually bottom)
    # 3. Remove it
    content = content.replace(m_export.group(0), "")
    
    # 4. Insert it immediately after the Reset or Filter button closing tag inside the form
    # We can search for `</button>` that belongs to resetBtn or filterBtn, or just the last </button> in the form.
    
    # Let us just find `<button type="button" id="resetBtn"[^>]*>.*?</button>` or similar
    m_reset = re.search(r'(<button[^>]*id="resetBtn"[^>]*>.*?</button>)', content, re.DOTALL)
    if m_reset:
        content = content.replace(m_reset.group(1), m_reset.group(1) + "\n                    " + export_html)
    else:
        # If there is no resetBtn, find filterBtn
        m_filter = re.search(r'(<button[^>]*type="submit"[^>]*>.*?</button>)', content, re.DOTALL)
        if m_filter:
            content = content.replace(m_filter.group(1), m_filter.group(1) + "\n                    " + export_html)
        else:
            print(f"Could not find anchor button in {filepath}")
            
    if content != original:
        with open(filepath, "w", encoding="utf-8") as f:
            f.write(content)
        print(f"Moved inline in {filepath}")

