import PyPDF2
import os

pdf_files = [
    "code-du-travail.pdf",
    "Benin-Code-securite-sociale-2003-MAJ-2007.pdf",
    "Acte uniforme relatif au droit comptable et à l’information financière (AUDCIF) - audcif_jo-ohada-15-02-2017.pdf",
    "6984ebbbb7bc0_Bénin-Code Général des Impôts 2026 (1).pdf"
]

for pdf_file in pdf_files:
    path = os.path.join(r"c:\xampp\htdocs\Para", pdf_file)
    try:
        with open(path, "rb") as f:
            reader = PyPDF2.PdfReader(f)
            num_pages = len(reader.pages)
            
            # Extract first page to see if there's text
            first_page_text = reader.pages[0].extract_text()
            if not first_page_text or first_page_text.strip() == "":
                text_status = "No text found (likely scanned)"
            else:
                text_status = f"Text found (starts with: {first_page_text[:50]}...)"
                
            print(f"File: {pdf_file}")
            print(f"Pages: {num_pages}")
            print(f"Status: {text_status}")
            print("-" * 40)
    except Exception as e:
        print(f"Error reading {pdf_file}: {e}")
