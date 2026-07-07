<?php

namespace Database\Seeders;

use App\Models\AccountingAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SyscohadaMetadataSeeder extends Seeder
{
    /**
     * Enrichit les comptes SYSCOHADA existants avec les métadonnées
     * (nature, is_summary, allow_journal_entry, reconciliable, etc.)
     */
    public function run(): void
    {
        $this->command?->info('Enrichissement des métadonnées SYSCOHADA...');

        $updated = 0;
        AccountingAccount::where('client_id', 0)
            ->where('is_syscohada', true)
            ->chunk(100, function ($accounts) use (&$updated) {
                foreach ($accounts as $account) {
                    $meta = $this->getMetadata($account);
                    if ($meta) {
                        $account->updateQuietly($meta);
                        $updated++;
                    }
                }
            });

        $this->command?->info("✓ {$updated} comptes enrichis.");
    }

    /**
     * Détermine les métadonnées pour un compte selon son code et type.
     */
    protected function getMetadata(AccountingAccount $account): ?array
    {
        $code = $account->code;
        $class = $account->syscohada_class;
        $type = $account->type;
        $meta = [];

        // === account_nature ===
        $meta['account_nature'] = match ($type) {
            'asset' => 'debitor',
            'contra_asset' => 'creditor',
            'liability', 'equity' => 'creditor',
            'revenue' => 'creditor',
            'expense' => 'debitor',
            default => 'bilateral',
        };

        // === is_summary ===
        $summaryPatterns = [
            '1', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19',
            '2', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29',
            '3', '30', '31', '32', '33', '34', '35', '36', '37', '38', '39',
            '4', '40', '41', '42', '43', '44', '45', '46', '47', '48', '49',
            '5', '50', '51', '52', '53', '54', '55', '56', '57', '58', '59',
            '6', '60', '61', '62', '63', '64', '65', '66', '67', '68', '69',
            '7', '70', '71', '72', '73', '74', '75', '76', '77', '78', '79',
            '8', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89',
            '9', '90', '91', '92', '93', '94', '95', '96', '97', '98', '99',
        ];

        $numCode = (int) $code;
        $meta['is_summary'] = in_array($code, $summaryPatterns, true)
            || (strlen($code) <= 2 && ctype_digit($code));

        // === allow_journal_entry ===
        $meta['allow_journal_entry'] = !$meta['is_summary'];

        // === reconciliable ===
        $reconciliableCodes = ['311', '3111', '3113', '321', '3211', '3212',
            '324', '401', '4011', '4013', '1212'];
        $meta['reconciliable'] = in_array($code, $reconciliableCodes, true);

        // === has_tva / tva_rate ===
        $tvaCodes = ['301', '302', '303'];
        if (in_array($code, $tvaCodes, true)) {
            $meta['has_tva'] = true;
            $meta['tva_rate'] = 18;
        }

        // === sort_order ===
        $meta['sort_order'] = $numCode;

        return $meta;
    }
}
