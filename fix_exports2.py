
import glob
import re

files = glob.glob("resources/views/modules/reports/**/*.blade.php", recursive=True)

for filepath in files:
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()
        
    original = content
    
    # We will search for the entire group of buttons line by line.
    lines = content.split("\n")
    start_idx = -1
    end_idx = -1
    
    excel_url = None
    excel_id = None
    pdf_url = None
    pdf_id = None
    print_url = None
    print_id = None
    
    in_button_group = False
    
    for i, line in enumerate(lines):
        if "<a " in line or "<button " in line:
            if "Excel" in line or "excel" in line.lower() or "exportExcel" in line:
                if start_idx == -1:
                    start_idx = i
                # Extract href
                m_href = re.search(r'href="([^"]+)"', line)
                if m_href: excel_url = m_href.group(1)
                m_id = re.search(r'id="([^"]+)"', line)
                if m_id: excel_id = m_id.group(1)
                
            elif "PDF" in line or "pdf" in line.lower() or "exportPdf" in line:
                if start_idx != -1:
                    m_href = re.search(r'href="([^"]+)"', line)
                    if m_href: pdf_url = m_href.group(1)
                    m_id = re.search(r'id="([^"]+)"', line)
                    if m_id: pdf_id = m_id.group(1)
                    
            elif "Print" in line or "print" in line.lower() or "exportPrint" in line:
                if start_idx != -1:
                    m_href = re.search(r'href="([^"]+)"', line)
                    if m_href: print_url = m_href.group(1)
                    m_id = re.search(r'id="([^"]+)"', line)
                    if m_id: print_id = m_id.group(1)
                    end_idx = i
                    break # Assuming one group per file
                    
    # Sometimes they span multiple lines, e.g. </a> is on the next line.
    if start_idx != -1 and end_idx != -1:
        # Find the actual end of the Print tag
        for j in range(end_idx, len(lines)):
            if "</a>" in lines[j] or "</button>" in lines[j]:
                end_idx = j
                break
                
        # Now construct replacement
        props = []
        if excel_url: props.append(f'excelUrl="{excel_url}"')
        if pdf_url: props.append(f'pdfUrl="{pdf_url}"')
        if print_url: props.append(f'printUrl="{print_url}"')
        if excel_id: props.append(f'excelId="{excel_id}"')
        if pdf_id: props.append(f'pdfId="{pdf_id}"')
        if print_id: props.append(f'printId="{print_id}"')
        
        replacement = " " * 20 + "<x-erp.export-buttons \n" + " " * 24 + ("\n" + " " * 24).join(props) + "\n" + " " * 20 + "/>"
        
        lines = lines[:start_idx] + [replacement] + lines[end_idx+1:]
        
        content = "\n".join(lines)
        if content != original:
            with open(filepath, "w", encoding="utf-8") as f:
                f.write(content)
            print(f"Refactored {filepath}")

