import pandas as pd

def extract_identifiers(file_path):
    try:
        xls = pd.ExcelFile(file_path)
        
        # 1. Look in CONEXION
        if 'CONEXION' in xls.sheet_names:
            df = pd.read_excel(xls, sheet_name='CONEXION')
            print("\n--- IDENTIFIANTS (Feuille CONEXION) ---")
            print(df.dropna(how='all').head(20).to_string())
            
        # 2. Look in JOURNAL for unique users
        if 'JOURNAL' in xls.sheet_names:
            df = pd.read_excel(xls, sheet_name='JOURNAL')
            if 'UTILISATEUR' in df.columns and 'MOT DE PASSE' in df.columns:
                users = df[['UTILISATEUR', 'MOT DE PASSE']].drop_duplicates().dropna(how='all')
                print("\n--- UTILISATEURS DU JOURNAL ---")
                print(users.to_string())
                
        # 3. Look in LISTES DU PERSONNEL
        if 'LISTES DU PERSONNEL' in xls.sheet_names:
            df = pd.read_excel(xls, sheet_name='LISTES DU PERSONNEL', nrows=20)
            print("\n--- LISTES DU PERSONNEL (Aperçu) ---")
            print(df.dropna(how='all', axis=1).dropna(how='all', axis=0).head(10).to_string())
            
    except Exception as e:
        print(f"Error: {e}")

if __name__ == '__main__':
    extract_identifiers('CCT GESTION.xlsm')
