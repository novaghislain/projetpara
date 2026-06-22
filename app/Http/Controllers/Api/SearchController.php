<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Employee;
use App\Models\JournalEntry;
use App\Models\ErpInvoice;
use App\Models\CompanyInvoice;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));
        $type = $request->get('type'); // optional filter: clients, invoices, etc.

        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $results = collect();

        // Helper: wrap matches in <mark>
        $highlight = fn($text) => preg_replace('/(' . preg_quote($q, '/') . ')/iu', '<mark>$1</mark>', e($text));

        // ── Clients ──
        if (!$type || $type === 'clients') {
            $clients = Client::where(function ($query) use ($q) {
                $query->where('company_name', 'LIKE', "%{$q}%")
                      ->orWhere('raison_sociale', 'LIKE', "%{$q}%")
                      ->orWhere('email', 'LIKE', "%{$q}%")
                      ->orWhere('ifu', 'LIKE', "%{$q}%")
                      ->orWhere('rccm', 'LIKE', "%{$q}%");
            })->limit(10)->get(['id', 'company_name', 'raison_sociale', 'email', 'ifu']);

            if ($clients->count()) {
                $results->push([
                    'category' => 'clients',
                    'items' => $clients->map(fn($c) => [
                        'title' => $highlight($c->company_name ?? $c->raison_sociale ?? 'Sans nom'),
                        'subtitle' => 'IFU: ' . ($c->ifu ?? 'N/A') . ' | ' . ($c->email ?? ''),
                        'icon' => 'bi-building',
                        'route' => "/clients/{$c->id}",
                        'badge' => 'Client',
                    ])->toArray(),
                ]);
            }
        }

        // ── Factures ERP ──
        if (!$type || $type === 'invoices') {
            $invoices = ErpInvoice::where(function ($query) use ($q) {
                $query->where('invoice_number', 'LIKE', "%{$q}%")
                      ->orWhere('client_name', 'LIKE', "%{$q}%")
                      ->orWhere('total_ttc', 'LIKE', "%{$q}%");
            })->limit(10)->get(['id', 'invoice_number', 'client_name', 'total_ttc', 'status']);

            if ($invoices->count()) {
                $results->push([
                    'category' => 'invoices',
                    'items' => $invoices->map(fn($i) => [
                        'title' => $highlight($i->invoice_number),
                        'subtitle' => $i->client_name . ' — ' . number_format($i->total_ttc, 0, ',', ' ') . ' FCFA',
                        'icon' => 'bi-receipt',
                        'route' => "/erp/invoices/{$i->id}",
                        'badge' => $i->status,
                    ])->toArray(),
                ]);
            }
        }

        // ── Écritures comptables ──
        if (!$type || $type === 'transactions') {
            $entries = JournalEntry::where(function ($query) use ($q) {
                $query->where('libelle', 'LIKE', "%{$q}%")
                      ->orWhere('piece_number', 'LIKE', "%{$q}%")
                      ->orWhere('montant', 'LIKE', "%{$q}%");
            })->limit(10)->get(['id', 'libelle', 'piece_number', 'montant', 'date']);

            if ($entries->count()) {
                $results->push([
                    'category' => 'transactions',
                    'items' => $entries->map(fn($e) => [
                        'title' => $highlight($e->libelle ?? 'Sans libellé'),
                        'subtitle' => 'Pièce: ' . ($e->piece_number ?? 'N/A') . ' | ' . number_format($e->montant, 0, ',', ' ') . ' FCFA',
                        'icon' => 'bi-journal-text',
                        'route' => "/accounting/entries/{$e->id}",
                        'badge' => $e->date,
                    ])->toArray(),
                ]);
            }
        }

        // ── Contacts ──
        if (!$type || $type === 'contacts') {
            $contacts = Contact::where(function ($query) use ($q) {
                $query->where('nom', 'LIKE', "%{$q}%")
                      ->orWhere('prenom', 'LIKE', "%{$q}%")
                      ->orWhere('email', 'LIKE', "%{$q}%")
                      ->orWhere('telephone', 'LIKE', "%{$q}%");
            })->limit(10)->get(['id', 'nom', 'prenom', 'email', 'telephone']);

            if ($contacts->count()) {
                $results->push([
                    'category' => 'contacts',
                    'items' => $contacts->map(fn($c) => [
                        'title' => $highlight(trim($c->nom . ' ' . $c->prenom)),
                        'subtitle' => $c->email ?? $c->telephone ?? '',
                        'icon' => 'bi-people',
                        'route' => "/contacts/{$c->id}",
                    ])->toArray(),
                ]);
            }
        }

        // ── Employés ──
        if (!$type || $type === 'employees') {
            $employees = Employee::where(function ($query) use ($q) {
                $query->where('nom', 'LIKE', "%{$q}%")
                      ->orWhere('prenom', 'LIKE', "%{$q}%")
                      ->orWhere('poste', 'LIKE', "%{$q}%")
                      ->orWhere('email', 'LIKE', "%{$q}%");
            })->limit(10)->get(['id', 'nom', 'prenom', 'poste', 'email']);

            if ($employees->count()) {
                $results->push([
                    'category' => 'employees',
                    'items' => $employees->map(fn($e) => [
                        'title' => $highlight(trim($e->nom . ' ' . $e->prenom)),
                        'subtitle' => $e->poste ?? $e->email ?? '',
                        'icon' => 'bi-person-badge',
                        'route' => "/rh/employees/{$e->id}",
                    ])->toArray(),
                ]);
            }
        }

        // ── Navigation (pages statiques) ──
        if (!$type || $type === 'navigation') {
            $pages = $this->getNavigationPages($q, $highlight);
            if (count($pages)) {
                $results->push([
                    'category' => 'navigation',
                    'items' => $pages,
                ]);
            }
        }

        return response()->json(['results' => $results->values()]);
    }

    protected function getNavigationPages(string $q, callable $highlight): array
    {
        $pages = [
            ['title' => 'Accueil',           'route' => '/dashboard',       'icon' => 'bi-house',       'keywords' => 'accueil home dashboard tableau bord'],
            ['title' => 'Clients',           'route' => '/clients',         'icon' => 'bi-building',    'keywords' => 'clients client crm'],
            ['title' => 'Comptabilité',      'route' => '/accounting',      'icon' => 'bi-calculator',  'keywords' => 'compta comptabilité accounting journal balance'],
            ['title' => 'Plan comptable',    'route' => '/accounting/accounts', 'icon' => 'bi-book',  'keywords' => 'plan comptable accounts'],
            ['title' => 'Télédéclarations',  'route' => '/tele-declarations', 'icon' => 'bi-file-earmark-text', 'keywords' => 'teledeclaration tva irpp cnss fiscal'],
            ['title' => 'Factures ERP',      'route' => '/erp/invoices',    'icon' => 'bi-receipt',     'keywords' => 'factures erp invoices facture'],
            ['title' => 'RH & Paie',         'route' => '/rh',              'icon' => 'bi-people',      'keywords' => 'rh paie employés payroll'],
            ['title' => 'IT Support',        'route' => '/it/tickets',      'icon' => 'bi-laptop',      'keywords' => 'it support helpdesk tickets'],
            ['title' => 'Juridique',         'route' => '/juridique',       'icon' => 'bi-briefcase',   'keywords' => 'juridique legal droit contrats'],
            ['title' => 'GED / Documents',   'route' => '/dossiers',        'icon' => 'bi-folder2-open','keywords' => 'ged documents dossiers doc'],
            ['title' => 'Missions',          'route' => '/missions',        'icon' => 'bi-check2-square','keywords' => 'missions pôles poles'],
            ['title' => 'Caisse',            'route' => '/erp/treasury',    'icon' => 'bi-cash-stack',  'keywords' => 'caisse treasury tresorerie'],
            ['title' => 'Commerce / POS',    'route' => '/commerce',        'icon' => 'bi-shop',        'keywords' => 'commerce pos vente stock'],
            ['title' => 'Tontines',          'route' => '/tontines',        'icon' => 'bi-piggy-bank',  'keywords' => 'tontine microfinance'],
            ['title' => 'Sécurité',          'route' => '/securite',        'icon' => 'bi-shield-lock', 'keywords' => 'sécurité 2fa audit security'],
            ['title' => 'Paramètres',        'route' => '/settings',        'icon' => 'bi-gear',        'keywords' => 'paramètres settings profil profile'],
        ];

        $qLow = mb_strtolower($q);
        return array_values(array_filter(array_map(function ($p) use ($qLow, $highlight) {
            if (mb_strpos(mb_strtolower($p['title']), $qLow) !== false
                || mb_strpos(mb_strtolower($p['keywords']), $qLow) !== false) {
                $p['title'] = $highlight($p['title']);
                unset($p['keywords']);
                return $p;
            }
            return null;
        }, $pages)));
    }
}
