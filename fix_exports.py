
import os
import re

reports_dir = "resources/views/modules/reports"
import glob

files = glob.glob(f"{reports_dir}/**/*.blade.php", recursive=True)

for filepath in files:
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()
        
    original = content
    
    # We need to find groups of 3 buttons: Excel, PDF, Print
    # They can be <a> or <button>. They have ids like exportExcel, exportPdf, exportPrint.
    # We will use regex to find the block containing these 3 buttons and replace it.
    
    # Pattern 1: <a id="exportExcel" href="..." class="...">...<i ...></i> Excel</a>
    # Pattern 2: <a href="..." class="...">...<i ...></i> Export Excel</a>
    
    # Let us find the URL for Excel, PDF, Print, and the ID if exists.
    
    # Match an <a> tag that contains Excel
    pattern_excel = re.compile(r'<a\s+(?:id="([^"]+)"\s+)?href="([^"]+)"[^>]*>.*?Excel.*?</a>', re.IGNORECASE | re.DOTALL)
    pattern_pdf = re.compile(r'<a\s+(?:id="([^"]+)"\s+)?href="([^"]+)"[^>]*>.*?PDF.*?</a>', re.IGNORECASE | re.DOTALL)
    pattern_print = re.compile(r'<a\s+(?:id="([^"]+)"\s+)?href="([^"]+)"[^>]*>.*?Print.*?</a>', re.IGNORECASE | re.DOTALL)
    
    excel_match = pattern_excel.search(content)
    pdf_match = pattern_pdf.search(content)
    print_match = pattern_print.search(content)
    
    if excel_match and pdf_match and print_match:
        excel_id, excel_url = excel_match.groups()
        pdf_id, pdf_url = pdf_match.groups()
        print_id, print_url = print_match.groups()
        
        # Now we need to remove the original 3 <a> tags.
        # We can just replace the whole block spanning from excel_match.start() to print_match.end()
        start = min(excel_match.start(), pdf_match.start(), print_match.start())
        end = max(excel_match.end(), pdf_match.end(), print_match.end())
        
        props = []
        if excel_url:
            props.append(f'excelUrl="{excel_url}"')
        if pdf_url:
            props.append(f'pdfUrl="{pdf_url}"')
        if print_url:
            props.append(f'printUrl="{print_url}"')
            
        if excel_id:
            props.append(f'excelId="{excel_id}"')
        if pdf_id:
            props.append(f'pdfId="{pdf_id}"')
        if print_id:
            props.append(f'printId="{print_id}"')
            
        props_str = "\n    ".join(props)
        replacement = f"<x-erp.export-buttons \n    {props_str}\n/>"
        
        content = content[:start] + replacement + content[end:]
        
        if content != original:
            with open(filepath, "w", encoding="utf-8") as f:
                f.write(content)
            print(f"Refactored {filepath}")

