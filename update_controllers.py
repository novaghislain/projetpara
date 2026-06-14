import os

files_map = {
    'app/Http/Controllers/Eden/Erp/CatalogueController.php': ("return inertia('Eden/Erp/Catalogue'", "return view('app', ['page' => 'erp-catalogue']"),
    'app/Http/Controllers/Eden/Erp/StockController.php': ("return inertia('Eden/Erp/Stock'", "return view('app', ['page' => 'erp-stock']"),
    'app/Http/Controllers/Eden/Erp/InvoiceController.php': ("return inertia('Eden/Erp/Invoice'", "return view('app', ['page' => 'erp-invoice']"),
    'app/Http/Controllers/Eden/Erp/EmployeeController.php': ("return inertia('Eden/Erp/Hr'", "return view('app', ['page' => 'erp-hr']"),
    'app/Http/Controllers/Eden/Erp/TreasuryController.php': ("return inertia('Eden/Erp/Treasury'", "return view('app', ['page' => 'erp-treasury']"),
}

for filepath, (search, replace) in files_map.items():
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    content = content.replace(search, replace)
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)
    print(f'Updated {os.path.basename(filepath)}')
