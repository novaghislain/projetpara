import zipfile
import xml.etree.ElementTree as ET
import sys

def extract_text_from_docx(docx_path, out_path):
    try:
        with zipfile.ZipFile(docx_path) as docx:
            xml_content = docx.read('word/document.xml')
            tree = ET.XML(xml_content)
            
            # define namespaces
            namespaces = {'w': 'http://schemas.openxmlformats.org/wordprocessingml/2006/main'}
            
            texts = []
            for paragraph in tree.findall('.//w:p', namespaces):
                para_text = []
                for run in paragraph.findall('.//w:t', namespaces):
                    if run.text:
                        para_text.append(run.text)
                if para_text:
                    texts.append(''.join(para_text))
                    
            with open(out_path, 'w', encoding='utf-8') as f:
                f.write('\n'.join(texts))
        print("Success")
    except Exception as e:
        print(f"Error: {e}")

if __name__ == '__main__':
    extract_text_from_docx('GEL_Cahier_des_Charges_Maitre_Unifie b.docx', 'cahier_des_charges.txt')
