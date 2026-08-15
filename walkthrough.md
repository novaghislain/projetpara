
## 3. Action 13 bis : Refonte des rapports comptables (SaaS Tenant)
Conformément à vos instructions, j'ai migré de force les **6 services de génération de rapports** :
- `BalanceSheetService.php` (Bilan)
- `IncomeStatementService.php` (Compte de résultat)
- `CashFlowStatementService.php` (Flux de trésorerie)
- `TrialBalanceService.php` (Balance de vérification)
- `GeneralLedgerService.php` (Grand Livre)
- `BalanceReportService.php` (Balance Générale)

**Toutes ces classes s'appuient désormais sur la nouvelle modélisation SaaS (multi-tenant) :**
- Utilisation de `gel_ecritures` (au lieu de `journal_entries`)
- Utilisation de `gel_lignes_ecriture` (au lieu de `entry_lines`)
- Utilisation de `gel_account_types` (le plan comptable maître partagé, au lieu de `accounting_accounts` restreint)
- Filtrage complet par `client_id` via les écritures pour l'isolation stricte des données du cabinet.

> [!TIP]
> La dette technique concernant l'ancienne modélisation comptable en anglais est désormais purgée du coeur des rapports financiers. Le moteur de calcul financier est prêt pour la production.
