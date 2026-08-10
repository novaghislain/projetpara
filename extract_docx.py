import docx
import sys
import os

def extract_text_from_docx(file_path):
    try:
        doc = docx.Document(file_path)
        full_text = []
        for para in doc.paragraphs:
            if para.text.strip() != "":
                full_text.append(para.text)
        return '\n'.join(full_text)
    except Exception as e:
        return f"Error reading {file_path}: {e}"

if __name__ == '__main__':
    global_docx = r"c:\xampp\htdocs\Para\GEL_Cahier_des_Charges_Global.docx"
    detail_docx = r"c:\xampp\htdocs\Para\GEL_Cahier_Detaille_Developpement.docx"
    
    with open(r"c:\xampp\htdocs\Para\Cahier_Global.md", "w", encoding="utf-8") as f:
        f.write("# Cahier des Charges Global\n\n")
        f.write(extract_text_from_docx(global_docx))
        
    with open(r"c:\xampp\htdocs\Para\Cahier_Detaille.md", "w", encoding="utf-8") as f:
        f.write("# Cahier Détaillé de Développement\n\n")
        f.write(extract_text_from_docx(detail_docx))
        
    print("Extraction complete. Check Cahier_Global.md and Cahier_Detaille.md")
