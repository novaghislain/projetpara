import PyPDF2
import os

pdf_files = [
    "code-du-travail.pdf",
    "Benin-Code-securite-sociale-2003-MAJ-2007.pdf",
    "6984ebbbb7bc0_Bénin-Code Général des Impôts 2026 (1).pdf"
]

for pdf_file in pdf_files:
    path = os.path.join(r"c:\xampp\htdocs\Para", pdf_file)
    out_path = os.path.join(r"c:\xampp\htdocs\Para\tmp", pdf_file.replace('.pdf', '.txt'))
    try:
        with open(path, "rb") as f:
            reader = PyPDF2.PdfReader(f)
            
            # Extract first 10 pages for consultation (to avoid massive files and OOM)
            text = []
            for i in range(min(10, len(reader.pages))):
                page_text = reader.pages[i].extract_text()
                if page_text:
                    text.append(page_text)
                    
            with open(out_path, "w", encoding="utf-8") as out_f:
                out_f.write("\n\n".join(text))
            
            print(f"Extracted first 10 pages of {pdf_file} to {out_path}")
    except Exception as e:
        print(f"Error reading {pdf_file}: {e}")
