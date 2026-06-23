<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\AccountingHotelFacture;
use App\Models\AccountingScolaireFacture;
use App\Models\AccountingQuittance;
use App\Models\AccountingCotisation;
use App\Models\AccountingTransitDossier;
use App\Models\AccountingEmballage;
use App\Models\AccountingHotelChambre;
use App\Models\AccountingHotelReservation;
use App\Models\AccountingScolaireEleve;
use App\Models\AccountingLocationBien;
use App\Models\AccountingTontine;
use App\Models\AccountingPressingCommande;
use App\Models\AccountingMorgueDepot;
use App\Models\AccountingMorgueFacture;
use App\Models\AccountingGrilleTarifaire;
use App\Models\AccountingCommission;
use App\Models\AccountingMobileTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DomainAccountingController extends Controller
{
    private function getClientId()
    {
        $user = Auth::user();
        if (!$user->client_id) abort(403, 'Aucune entreprise associée.');
        return $user->client_id;
    }

    // ═══════════════════════════════════════════════════════════════
    // HÔTEL — Factures
    // ═══════════════════════════════════════════════════════════════

    public function hotelFactures(Request $request)
    {
        $clientId = $this->getClientId();
        $items = AccountingHotelFacture::where('client_id', $clientId)
            ->orderBy('created_at', 'desc')->take(200)->get()->map(fn($e) => [
                'id' => $e->id, 'numero_facture' => $e->numero_facture, 'type' => $e->type,
                'client_nom' => $e->client_nom, 'chambre' => $e->chambre,
                'date_arrivee' => $e->date_arrivee?->format('Y-m-d'), 'date_depart' => $e->date_depart?->format('Y-m-d'),
                'nb_nuitees' => $e->nb_nuitees, 'prix_nuitee' => (float) $e->prix_nuitee,
                'montant_ht' => (float) $e->montant_ht, 'tva' => (float) $e->tva,
                'taxe_sejour' => (float) $e->taxe_sejour, 'montant_ttc' => (float) $e->montant_ttc,
                'montant_paye' => (float) $e->montant_paye, 'solde' => (float) $e->solde,
                'statut' => $e->statut,
            ]);
        return response()->json($items);
    }

    public function storeHotelFacture(Request $request)
    {
        $clientId = $this->getClientId();
        $v = $request->validate([
            'numero_facture' => 'required|string|max:50',
            'type' => 'required|in:chambre,restauration,service,autre',
            'client_nom' => 'nullable|string|max:255',
            'chambre' => 'nullable|string|max:50',
            'date_arrivee' => 'nullable|date', 'date_depart' => 'nullable|date',
            'nb_nuitees' => 'nullable|integer|min:1',
            'prix_nuitee' => 'nullable|numeric|min:0',
            'montant_ht' => 'nullable|numeric|min:0', 'tva' => 'nullable|numeric|min:0',
            'taxe_sejour' => 'nullable|numeric|min:0', 'montant_ttc' => 'nullable|numeric|min:0',
            'montant_paye' => 'nullable|numeric|min:0', 'solde' => 'nullable|numeric',
            'statut' => 'nullable|in:en_attente,payee,partielle,annulee',
        ]);
        $v['client_id'] = $clientId; $v['created_by'] = Auth::id();
        $v['montant_ttc'] ??= ($v['montant_ht'] ?? 0) + ($v['tva'] ?? 0) + ($v['taxe_sejour'] ?? 0);
        $v['solde'] ??= ($v['montant_ttc'] ?? 0) - ($v['montant_paye'] ?? 0);
        $item = AccountingHotelFacture::create($v);
        return response()->json(['message' => 'Facture hôtel créée.', 'item' => $item], 201);
    }

    public function deleteHotelFacture($id)
    {
        $item = AccountingHotelFacture::where('client_id', $this->getClientId())->findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Facture supprimée.']);
    }

    // ═══════════════════════════════════════════════════════════════
    // SCOLAIRE — Factures
    // ═══════════════════════════════════════════════════════════════

    public function scolaireFactures(Request $request)
    {
        $clientId = $this->getClientId();
        $items = AccountingScolaireFacture::where('client_id', $clientId)
            ->orderBy('created_at', 'desc')->take(200)->get()->map(fn($e) => [
                'id' => $e->id, 'numero_facture' => $e->numero_facture,
                'annee_scolaire' => $e->annee_scolaire, 'eleve_nom' => $e->eleve_nom,
                'eleve_prenom' => $e->eleve_prenom, 'classe' => $e->classe,
                'matricule' => $e->matricule, 'type_frais' => $e->type_frais,
                'periode' => $e->periode, 'montant_du' => (float) $e->montant_du,
                'remise' => (float) $e->remise, 'montant_net' => (float) $e->montant_net,
                'montant_paye' => (float) $e->montant_paye, 'solde' => (float) $e->solde,
                'statut' => $e->statut, 'date_echeance' => $e->date_echeance?->format('Y-m-d'),
            ]);
        return response()->json($items);
    }

    public function storeScolaireFacture(Request $request)
    {
        $clientId = $this->getClientId();
        $v = $request->validate([
            'numero_facture' => 'required|string|max:50',
            'annee_scolaire' => 'required|string|max:20',
            'eleve_nom' => 'required|string|max:255',
            'eleve_prenom' => 'nullable|string|max:255',
            'classe' => 'required|string|max:100',
            'matricule' => 'nullable|string|max:50',
            'type_frais' => 'required|in:scolarite,inscription,cantine,pension,transport,tenue,autre',
            'periode' => 'nullable|string|max:50',
            'montant_du' => 'nullable|numeric|min:0',
            'remise' => 'nullable|numeric|min:0',
            'montant_net' => 'nullable|numeric|min:0',
            'montant_paye' => 'nullable|numeric|min:0',
            'statut' => 'nullable|in:en_attente,partielle,payee,annulee,impayee',
            'date_echeance' => 'nullable|date',
        ]);
        $v['client_id'] = $clientId; $v['created_by'] = Auth::id();
        $v['montant_net'] ??= ($v['montant_du'] ?? 0) - ($v['remise'] ?? 0);
        $v['solde'] ??= ($v['montant_net'] ?? 0) - ($v['montant_paye'] ?? 0);
        $item = AccountingScolaireFacture::create($v);
        return response()->json(['message' => 'Facture scolaire créée.', 'item' => $item], 201);
    }

    public function deleteScolaireFacture($id)
    {
        $item = AccountingScolaireFacture::where('client_id', $this->getClientId())->findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Facture supprimée.']);
    }

    // ═══════════════════════════════════════════════════════════════
    // LOCATION — Quittances
    // ═══════════════════════════════════════════════════════════════

    public function quittances(Request $request)
    {
        $clientId = $this->getClientId();
        $items = AccountingQuittance::where('client_id', $clientId)
            ->orderBy('created_at', 'desc')->take(200)->get()->map(fn($e) => [
                'id' => $e->id, 'numero_quittance' => $e->numero_quittance,
                'bien' => $e->bien, 'locataire_nom' => $e->locataire_nom,
                'periode' => $e->periode, 'date_debut' => $e->date_debut?->format('Y-m-d'),
                'date_fin' => $e->date_fin?->format('Y-m-d'),
                'loyer_ht' => (float) $e->loyer_ht, 'charges' => (float) $e->charges,
                'tva' => (float) $e->tva, 'montant_total' => (float) $e->montant_total,
                'montant_paye' => (float) $e->montant_paye, 'solde' => (float) $e->solde,
                'statut' => $e->statut,
            ]);
        return response()->json($items);
    }

    public function storeQuittance(Request $request)
    {
        $clientId = $this->getClientId();
        $v = $request->validate([
            'numero_quittance' => 'required|string|max:50',
            'bien' => 'nullable|string|max:255',
            'locataire_nom' => 'required|string|max:255',
            'periode' => 'required|string|max:50',
            'date_debut' => 'required|date', 'date_fin' => 'required|date',
            'loyer_ht' => 'nullable|numeric|min:0', 'charges' => 'nullable|numeric|min:0',
            'tva' => 'nullable|numeric|min:0', 'montant_total' => 'nullable|numeric|min:0',
            'montant_paye' => 'nullable|numeric|min:0',
            'statut' => 'nullable|in:en_attente,payee,partielle,impayee,annulee',
            'date_echeance' => 'nullable|date',
        ]);
        $v['client_id'] = $clientId; $v['created_by'] = Auth::id();
        $v['montant_total'] ??= ($v['loyer_ht'] ?? 0) + ($v['charges'] ?? 0) + ($v['tva'] ?? 0);
        $v['solde'] ??= ($v['montant_total'] ?? 0) - ($v['montant_paye'] ?? 0);
        $item = AccountingQuittance::create($v);
        return response()->json(['message' => 'Quittance créée.', 'item' => $item], 201);
    }

    public function deleteQuittance($id)
    {
        $item = AccountingQuittance::where('client_id', $this->getClientId())->findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Quittance supprimée.']);
    }

    // ═══════════════════════════════════════════════════════════════
    // TONTINE — Cotisations
    // ═══════════════════════════════════════════════════════════════

    public function cotisations(Request $request)
    {
        $clientId = $this->getClientId();
        $items = AccountingCotisation::where('client_id', $clientId)
            ->orderBy('created_at', 'desc')->take(200)->get()->map(fn($e) => [
                'id' => $e->id, 'tontine_nom' => $e->tontine_nom,
                'membre_nom' => $e->membre_nom, 'periode' => $e->periode,
                'date_echeance' => $e->date_echeance?->format('Y-m-d'),
                'montant' => (float) $e->montant, 'montant_paye' => (float) $e->montant_paye,
                'solde' => (float) $e->solde, 'statut' => $e->statut,
            ]);
        return response()->json($items);
    }

    public function storeCotisation(Request $request)
    {
        $clientId = $this->getClientId();
        $v = $request->validate([
            'tontine_nom' => 'required|string|max:255',
            'membre_nom' => 'required|string|max:255',
            'periode' => 'required|string|max:50',
            'date_echeance' => 'required|date',
            'montant' => 'required|numeric|min:0',
            'montant_paye' => 'nullable|numeric|min:0',
            'statut' => 'nullable|in:en_attente,payee,retard,annulee',
        ]);
        $v['client_id'] = $clientId; $v['created_by'] = Auth::id();
        $v['solde'] ??= ($v['montant'] ?? 0) - ($v['montant_paye'] ?? 0);
        $item = AccountingCotisation::create($v);
        return response()->json(['message' => 'Cotisation créée.', 'item' => $item], 201);
    }

    public function deleteCotisation($id)
    {
        $item = AccountingCotisation::where('client_id', $this->getClientId())->findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Cotisation supprimée.']);
    }

    // ═══════════════════════════════════════════════════════════════
    // TRANSPORT — Transit dossiers
    // ═══════════════════════════════════════════════════════════════

    public function transitDossiers(Request $request)
    {
        $clientId = $this->getClientId();
        $items = AccountingTransitDossier::where('client_id', $clientId)
            ->orderBy('created_at', 'desc')->take(200)->get()->map(fn($e) => [
                'id' => $e->id, 'reference_dossier' => $e->reference_dossier,
                'type_transit' => $e->type_transit,
                'fournisseur_nom' => $e->fournisseur_nom, 'marchandise' => $e->marchandise,
                'valeur_marchandise' => (float) $e->valeur_marchandise,
                'fret_ht' => (float) $e->fret_ht, 'droits_douane' => (float) $e->droits_douane,
                'tva_douane' => (float) $e->tva_douane,
                'total_facture' => (float) $e->total_facture,
                'montant_paye' => (float) $e->montant_paye, 'solde' => (float) $e->solde,
                'statut' => $e->statut, 'date_ouverture' => $e->date_ouverture?->format('Y-m-d'),
                'douane_bureau' => $e->douane_bureau,
            ]);
        return response()->json($items);
    }

    public function storeTransitDossier(Request $request)
    {
        $clientId = $this->getClientId();
        $v = $request->validate([
            'reference_dossier' => 'required|string|max:50',
            'type_transit' => 'required|in:import,export,transitaire,douane',
            'fournisseur_nom' => 'nullable|string|max:255',
            'marchandise' => 'nullable|string|max:255',
            'valeur_marchandise' => 'nullable|numeric|min:0',
            'fret_ht' => 'nullable|numeric|min:0',
            'droits_douane' => 'nullable|numeric|min:0',
            'tva_douane' => 'nullable|numeric|min:0',
            'frais_accessoires' => 'nullable|numeric|min:0',
            'montant_paye' => 'nullable|numeric|min:0',
            'statut' => 'nullable|in:en_cours,finalise,facture,paye,annule',
            'date_ouverture' => 'required|date',
            'douane_bureau' => 'nullable|string|max:255',
        ]);
        $v['client_id'] = $clientId; $v['created_by'] = Auth::id();
        $total = ($v['fret_ht'] ?? 0) + ($v['droits_douane'] ?? 0) + ($v['tva_douane'] ?? 0) + ($v['frais_accessoires'] ?? 0);
        $v['total_facture'] = ($v['valeur_marchandise'] ?? 0) + $total;
        $v['solde'] = $v['total_facture'] - ($v['montant_paye'] ?? 0);
        $item = AccountingTransitDossier::create($v);
        return response()->json(['message' => 'Dossier transit créé.', 'item' => $item], 201);
    }

    public function deleteTransitDossier($id)
    {
        $item = AccountingTransitDossier::where('client_id', $this->getClientId())->findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Dossier supprimé.']);
    }

    // ═══════════════════════════════════════════════════════════════
    // HÔTEL — Chambres
    // ═══════════════════════════════════════════════════════════════

    public function hotelChambres(Request $request)
    {
        $clientId = $this->getClientId();
        $items = AccountingHotelChambre::where('client_id', $clientId)
            ->orderBy('numero_chambre')->get()->map(fn($e) => [
                'id' => $e->id, 'numero_chambre' => $e->numero_chambre, 'type' => $e->type,
                'categorie' => $e->categorie, 'prix_nuitee' => (float) $e->prix_nuitee,
                'capacite' => $e->capacite, 'etage' => $e->etage, 'statut' => $e->statut,
                'equipements' => $e->equipements, 'is_active' => $e->is_active,
            ]);
        return response()->json($items);
    }

    public function storeHotelChambre(Request $request)
    {
        $clientId = $this->getClientId();
        $v = $request->validate([
            'numero_chambre' => 'required|string|max:50',
            'type' => 'required|in:standard,suite,deluxe,presidentielle',
            'categorie' => 'nullable|string|max:50',
            'prix_nuitee' => 'nullable|numeric|min:0',
            'capacite' => 'nullable|integer|min:1',
            'etage' => 'nullable|integer',
            'statut' => 'nullable|in:disponible,occupee,maintenance,reservee',
            'equipements' => 'nullable',
            'notes' => 'nullable|string',
        ]);
        $v['client_id'] = $clientId; $v['created_by'] = Auth::id();
        if (isset($v['equipements']) && is_string($v['equipements'])) $v['equipements'] = json_decode($v['equipements'], true);
        $item = AccountingHotelChambre::create($v);
        return response()->json(['message' => 'Chambre créée.', 'item' => $item], 201);
    }

    public function deleteHotelChambre($id)
    {
        $item = AccountingHotelChambre::where('client_id', $this->getClientId())->findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Chambre supprimée.']);
    }

    // ═══════════════════════════════════════════════════════════════
    // HÔTEL — Réservations
    // ═══════════════════════════════════════════════════════════════

    public function hotelReservations(Request $request)
    {
        $clientId = $this->getClientId();
        $items = AccountingHotelReservation::where('client_id', $clientId)
            ->orderBy('created_at', 'desc')->take(200)->get()->map(fn($e) => [
                'id' => $e->id, 'numero_reservation' => $e->numero_reservation,
                'chambre_id' => $e->chambre_id, 'chambre' => $e->chambre?->numero_chambre,
                'client_nom' => $e->client_nom, 'client_contact' => $e->client_contact,
                'date_arrivee' => $e->date_arrivee?->format('Y-m-d'),
                'date_depart' => $e->date_depart?->format('Y-m-d'),
                'nb_nuitees' => $e->nb_nuitees,
                'montant_total' => (float) $e->montant_total, 'acompte' => (float) $e->acompte,
                'solde' => (float) $e->solde, 'statut' => $e->statut, 'source' => $e->source,
            ]);
        return response()->json($items);
    }

    public function storeHotelReservation(Request $request)
    {
        $clientId = $this->getClientId();
        $v = $request->validate([
            'numero_reservation' => 'required|string|max:50',
            'chambre_id' => 'nullable|integer|exists:accounting_hotel_chambres,id',
            'client_nom' => 'required|string|max:255',
            'client_contact' => 'nullable|string|max:50',
            'client_email' => 'nullable|email|max:255',
            'date_arrivee' => 'required|date',
            'date_depart' => 'required|date',
            'nb_nuitees' => 'nullable|integer|min:1',
            'nb_adultes' => 'nullable|integer|min:1',
            'nb_enfants' => 'nullable|integer|min:0',
            'montant_total' => 'nullable|numeric|min:0',
            'acompte' => 'nullable|numeric|min:0',
            'statut' => 'nullable|in:confirmee,en_cours,terminee,annulee,no_show',
            'source' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);
        $v['client_id'] = $clientId; $v['created_by'] = Auth::id();
        $v['solde'] = ($v['montant_total'] ?? 0) - ($v['acompte'] ?? 0);
        $item = AccountingHotelReservation::create($v);
        return response()->json(['message' => 'Réservation créée.', 'item' => $item], 201);
    }

    public function deleteHotelReservation($id)
    {
        $item = AccountingHotelReservation::where('client_id', $this->getClientId())->findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Réservation supprimée.']);
    }

    // ═══════════════════════════════════════════════════════════════
    // SCOLAIRE — Élèves
    // ═══════════════════════════════════════════════════════════════

    public function scolaireEleves(Request $request)
    {
        $clientId = $this->getClientId();
        $items = AccountingScolaireEleve::where('client_id', $clientId)
            ->orderBy('nom')->get()->map(fn($e) => [
                'id' => $e->id, 'matricule' => $e->matricule, 'nom' => $e->nom,
                'prenom' => $e->prenom, 'classe' => $e->classe,
                'annee_scolaire' => $e->annee_scolaire, 'niveau' => $e->niveau,
                'statut' => $e->statut, 'nom_tuteur' => $e->nom_tuteur,
                'contact_tuteur' => $e->contact_tuteur,
            ]);
        return response()->json($items);
    }

    public function storeScolaireEleve(Request $request)
    {
        $clientId = $this->getClientId();
        $v = $request->validate([
            'matricule' => 'nullable|string|max:50',
            'nom' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'date_naissance' => 'nullable|date',
            'sexe' => 'nullable|in:M,F',
            'classe' => 'required|string|max:100',
            'annee_scolaire' => 'required|string|max:20',
            'niveau' => 'nullable|string|max:50',
            'statut' => 'nullable|in:actif,inactif,diplome,exclu',
            'nom_tuteur' => 'nullable|string|max:255',
            'contact_tuteur' => 'nullable|string|max:50',
            'email_tuteur' => 'nullable|email|max:255',
            'adresse' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        $v['client_id'] = $clientId; $v['created_by'] = Auth::id();
        $item = AccountingScolaireEleve::create($v);
        return response()->json(['message' => 'Élève créé.', 'item' => $item], 201);
    }

    public function deleteScolaireEleve($id)
    {
        $item = AccountingScolaireEleve::where('client_id', $this->getClientId())->findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Élève supprimé.']);
    }

    // ═══════════════════════════════════════════════════════════════
    // LOCATION — Biens
    // ═══════════════════════════════════════════════════════════════

    public function locationBiens(Request $request)
    {
        $clientId = $this->getClientId();
        $items = AccountingLocationBien::where('client_id', $clientId)
            ->orderBy('designation')->get()->map(fn($e) => [
                'id' => $e->id, 'reference_bien' => $e->reference_bien, 'designation' => $e->designation,
                'type' => $e->type, 'ville' => $e->ville, 'quartier' => $e->quartier,
                'nb_pieces' => $e->nb_pieces, 'loyer_mensuel' => (float) $e->loyer_mensuel,
                'statut' => $e->statut, 'locataire_actuel' => $e->locataire_actuel,
                'is_active' => $e->is_active,
            ]);
        return response()->json($items);
    }

    public function storeLocationBien(Request $request)
    {
        $clientId = $this->getClientId();
        $v = $request->validate([
            'reference_bien' => 'nullable|string|max:50',
            'designation' => 'required|string|max:255',
            'type' => 'required|in:appartement,maison,local,terrain,bureau',
            'adresse' => 'nullable|string',
            'ville' => 'nullable|string|max:100',
            'quartier' => 'nullable|string|max:100',
            'surface' => 'nullable|numeric|min:0',
            'nb_pieces' => 'nullable|integer|min:1',
            'loyer_mensuel' => 'nullable|numeric|min:0',
            'charges_mensuelles' => 'nullable|numeric|min:0',
            'caution' => 'nullable|numeric|min:0',
            'statut' => 'nullable|in:disponible,loue,en_travaux,reserve',
            'locataire_actuel' => 'nullable|string|max:255',
            'date_debut_bail' => 'nullable|date',
            'date_fin_bail' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        $v['client_id'] = $clientId; $v['created_by'] = Auth::id();
        $item = AccountingLocationBien::create($v);
        return response()->json(['message' => 'Bien créé.', 'item' => $item], 201);
    }

    public function deleteLocationBien($id)
    {
        $item = AccountingLocationBien::where('client_id', $this->getClientId())->findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Bien supprimé.']);
    }

    // ═══════════════════════════════════════════════════════════════
    // TONTINE — Groupes
    // ═══════════════════════════════════════════════════════════════

    public function tontines(Request $request)
    {
        $clientId = $this->getClientId();
        $items = AccountingTontine::where('client_id', $clientId)
            ->orderBy('nom_groupe')->get()->map(fn($e) => [
                'id' => $e->id, 'nom_groupe' => $e->nom_groupe, 'description' => $e->description,
                'nb_membres' => $e->nb_membres, 'montant_cotisation' => (float) $e->montant_cotisation,
                'frequence' => $e->frequence, 'montant_caisse' => (float) $e->montant_caisse,
                'date_creation' => $e->date_creation?->format('Y-m-d'), 'statut' => $e->statut,
            ]);
        return response()->json($items);
    }

    public function storeTontine(Request $request)
    {
        $clientId = $this->getClientId();
        $v = $request->validate([
            'nom_groupe' => 'required|string|max:255',
            'description' => 'nullable|string',
            'nb_membres' => 'nullable|integer|min:1',
            'montant_cotisation' => 'nullable|numeric|min:0',
            'frequence' => 'required|in:hebdomadaire,mensuelle,trimestrielle',
            'montant_caisse' => 'nullable|numeric|min:0',
            'date_creation' => 'nullable|date',
            'statut' => 'nullable|in:active,suspendue,terminee',
            'regles' => 'nullable|string',
        ]);
        $v['client_id'] = $clientId; $v['created_by'] = Auth::id();
        $item = AccountingTontine::create($v);
        return response()->json(['message' => 'Tontine créée.', 'item' => $item], 201);
    }

    public function deleteTontine($id)
    {
        $item = AccountingTontine::where('client_id', $this->getClientId())->findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Tontine supprimée.']);
    }

    // ═══════════════════════════════════════════════════════════════
    // PRESSING — Commandes
    // ═══════════════════════════════════════════════════════════════

    public function pressingCommandes(Request $request)
    {
        $clientId = $this->getClientId();
        $items = AccountingPressingCommande::where('client_id', $clientId)
            ->orderBy('created_at', 'desc')->take(200)->get()->map(fn($e) => [
                'id' => $e->id, 'numero_commande' => $e->numero_commande,
                'client_nom' => $e->client_nom, 'client_contact' => $e->client_contact,
                'date_depot' => $e->date_depot?->format('Y-m-d'),
                'date_retrait_prevu' => $e->date_retrait_prevu?->format('Y-m-d'),
                'date_retrait' => $e->date_retrait?->format('Y-m-d'),
                'nb_articles' => $e->nb_articles, 'type_service' => $e->type_service,
                'montant_total' => (float) $e->montant_total, 'acompte' => (float) $e->acompte,
                'solde' => (float) $e->solde, 'statut' => $e->statut,
            ]);
        return response()->json($items);
    }

    public function storePressingCommande(Request $request)
    {
        $clientId = $this->getClientId();
        $v = $request->validate([
            'numero_commande' => 'required|string|max:50',
            'client_nom' => 'required|string|max:255',
            'client_contact' => 'nullable|string|max:50',
            'date_depot' => 'required|date',
            'date_retrait_prevu' => 'nullable|date',
            'nb_articles' => 'nullable|integer|min:1',
            'articles' => 'nullable|string',
            'type_service' => 'nullable|in:nettoyage,repassage,teinturerie,couture',
            'montant_total' => 'nullable|numeric|min:0',
            'acompte' => 'nullable|numeric|min:0',
            'statut' => 'nullable|in:en_cours,pret,remis,annule',
            'notes' => 'nullable|string',
        ]);
        $v['client_id'] = $clientId; $v['created_by'] = Auth::id();
        $v['solde'] = ($v['montant_total'] ?? 0) - ($v['acompte'] ?? 0);
        $item = AccountingPressingCommande::create($v);
        return response()->json(['message' => 'Commande pressing créée.', 'item' => $item], 201);
    }

    public function deletePressingCommande($id)
    {
        $item = AccountingPressingCommande::where('client_id', $this->getClientId())->findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Commande supprimée.']);
    }

    // ═══════════════════════════════════════════════════════════════
    // MORGUE — Dépôts
    // ═══════════════════════════════════════════════════════════════

    public function morgueDepots(Request $request)
    {
        $clientId = $this->getClientId();
        $items = AccountingMorgueDepot::where('client_id', $clientId)
            ->orderBy('created_at', 'desc')->take(200)->get()->map(fn($e) => [
                'id' => $e->id, 'numero_dossier' => $e->numero_dossier,
                'defunt_nom' => $e->defunt_nom, 'defunt_prenom' => $e->defunt_prenom,
                'date_deces' => $e->date_deces?->format('Y-m-d'),
                'date_depot' => $e->date_depot?->format('Y-m-d'),
                'date_sortie' => $e->date_sortie?->format('Y-m-d'),
                'famille_nom' => $e->famille_nom, 'famille_contact' => $e->famille_contact,
                'type_conservation' => $e->type_conservation, 'nb_jours' => $e->nb_jours,
                'tarif_journalier' => (float) $e->tarif_journalier,
                'montant_total' => (float) $e->montant_total, 'montant_paye' => (float) $e->montant_paye,
                'solde' => (float) $e->solde, 'statut' => $e->statut,
            ]);
        return response()->json($items);
    }

    public function storeMorgueDepot(Request $request)
    {
        $clientId = $this->getClientId();
        $v = $request->validate([
            'numero_dossier' => 'required|string|max:50',
            'defunt_nom' => 'required|string|max:255',
            'defunt_prenom' => 'nullable|string|max:255',
            'date_deces' => 'nullable|date',
            'date_depot' => 'required|date',
            'date_sortie' => 'nullable|date',
            'famille_contact' => 'nullable|string|max:50',
            'famille_nom' => 'nullable|string|max:255',
            'type_conservation' => 'nullable|in:normale,refrigeree,embaumement',
            'nb_jours' => 'nullable|integer|min:0',
            'tarif_journalier' => 'nullable|numeric|min:0',
            'montant_total' => 'nullable|numeric|min:0',
            'montant_paye' => 'nullable|numeric|min:0',
            'statut' => 'nullable|in:en_cours,sorti,facture,annule',
            'notes' => 'nullable|string',
        ]);
        $v['client_id'] = $clientId; $v['created_by'] = Auth::id();
        $v['solde'] = ($v['montant_total'] ?? 0) - ($v['montant_paye'] ?? 0);
        $item = AccountingMorgueDepot::create($v);
        return response()->json(['message' => 'Dépôt morgue créé.', 'item' => $item], 201);
    }

    public function deleteMorgueDepot($id)
    {
        $item = AccountingMorgueDepot::where('client_id', $this->getClientId())->findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Dépôt supprimé.']);
    }

    // ═══════════════════════════════════════════════════════════════
    // MORGUE — Factures
    // ═══════════════════════════════════════════════════════════════

    public function morgueFactures(Request $request)
    {
        $clientId = $this->getClientId();
        $items = AccountingMorgueFacture::where('client_id', $clientId)
            ->orderBy('created_at', 'desc')->take(200)->get()->map(fn($e) => [
                'id' => $e->id, 'numero_facture' => $e->numero_facture,
                'depot_id' => $e->depot_id, 'client_nom' => $e->client_nom,
                'defunt_nom' => $e->defunt_nom, 'type_prestation' => $e->type_prestation,
                'nb_jours' => $e->nb_jours, 'montant_ht' => (float) $e->montant_ht,
                'tva' => (float) $e->tva, 'montant_ttc' => (float) $e->montant_ttc,
                'montant_paye' => (float) $e->montant_paye, 'solde' => (float) $e->solde,
                'statut' => $e->statut,
            ]);
        return response()->json($items);
    }

    public function storeMorgueFacture(Request $request)
    {
        $clientId = $this->getClientId();
        $v = $request->validate([
            'numero_facture' => 'required|string|max:50',
            'depot_id' => 'nullable|integer|exists:accounting_morgue_depots,id',
            'client_nom' => 'required|string|max:255',
            'defunt_nom' => 'required|string|max:255',
            'type_prestation' => 'nullable|in:conservation,embaumement,soins,transport,cerueil,service',
            'nb_jours' => 'nullable|integer|min:0',
            'montant_ht' => 'nullable|numeric|min:0',
            'tva' => 'nullable|numeric|min:0',
            'montant_ttc' => 'nullable|numeric|min:0',
            'montant_paye' => 'nullable|numeric|min:0',
            'statut' => 'nullable|in:en_attente,payee,partielle,annulee',
            'notes' => 'nullable|string',
        ]);
        $v['client_id'] = $clientId; $v['created_by'] = Auth::id();
        $v['montant_ttc'] ??= ($v['montant_ht'] ?? 0) + ($v['tva'] ?? 0);
        $v['solde'] = ($v['montant_ttc'] ?? 0) - ($v['montant_paye'] ?? 0);
        $item = AccountingMorgueFacture::create($v);
        return response()->json(['message' => 'Facture morgue créée.', 'item' => $item], 201);
    }

    public function deleteMorgueFacture($id)
    {
        $item = AccountingMorgueFacture::where('client_id', $this->getClientId())->findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Facture supprimée.']);
    }

    // ═══════════════════════════════════════════════════════════════
    // GRILLES TARIFAIRES
    // ═══════════════════════════════════════════════════════════════

    public function grillesTarifaires(Request $request)
    {
        $clientId = $this->getClientId();
        $items = AccountingGrilleTarifaire::where('client_id', $clientId)
            ->orderBy('designation')->get()->map(fn($e) => [
                'id' => $e->id, 'code' => $e->code, 'designation' => $e->designation,
                'categorie' => $e->categorie, 'unite' => $e->unite,
                'prix_unitaire' => (float) $e->prix_unitaire, 'tva' => (float) $e->tva,
                'remise_max' => (float) $e->remise_max, 'is_active' => $e->is_active,
            ]);
        return response()->json($items);
    }

    public function storeGrilleTarifaire(Request $request)
    {
        $clientId = $this->getClientId();
        $v = $request->validate([
            'code' => 'nullable|string|max:50',
            'designation' => 'required|string|max:255',
            'categorie' => 'nullable|string|max:50',
            'unite' => 'nullable|string|max:50',
            'prix_unitaire' => 'nullable|numeric|min:0',
            'tva' => 'nullable|numeric|min:0|max:100',
            'remise_max' => 'nullable|numeric|min:0|max:100',
            'date_validite_debut' => 'nullable|date',
            'date_validite_fin' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        $v['client_id'] = $clientId; $v['created_by'] = Auth::id();
        $item = AccountingGrilleTarifaire::create($v);
        return response()->json(['message' => 'Grille tarifaire créée.', 'item' => $item], 201);
    }

    public function deleteGrilleTarifaire($id)
    {
        $item = AccountingGrilleTarifaire::where('client_id', $this->getClientId())->findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Grille supprimée.']);
    }

    // ═══════════════════════════════════════════════════════════════
    // COMMISSIONS
    // ═══════════════════════════════════════════════════════════════

    public function commissions(Request $request)
    {
        $clientId = $this->getClientId();
        $items = AccountingCommission::where('client_id', $clientId)
            ->orderBy('created_at', 'desc')->take(200)->get()->map(fn($e) => [
                'id' => $e->id, 'numero_commission' => $e->numero_commission, 'type' => $e->type,
                'agent_nom' => $e->agent_nom, 'montant_base' => (float) $e->montant_base,
                'taux_commission' => (float) $e->taux_commission,
                'montant_commission' => (float) $e->montant_commission,
                'tva' => (float) $e->tva, 'montant_net' => (float) $e->montant_net,
                'montant_paye' => (float) $e->montant_paye, 'solde' => (float) $e->solde,
                'date_operation' => $e->date_operation?->format('Y-m-d'),
                'date_paiement' => $e->date_paiement?->format('Y-m-d'),
                'statut' => $e->statut,
            ]);
        return response()->json($items);
    }

    public function storeCommission(Request $request)
    {
        $clientId = $this->getClientId();
        $v = $request->validate([
            'numero_commission' => 'nullable|string|max:50',
            'type' => 'nullable|in:vente,prestation,courtage,intermediaire',
            'agent_nom' => 'required|string|max:255',
            'agent_contact' => 'nullable|string|max:50',
            'montant_base' => 'nullable|numeric|min:0',
            'taux_commission' => 'nullable|numeric|min:0|max:100',
            'montant_commission' => 'nullable|numeric|min:0',
            'tva' => 'nullable|numeric|min:0',
            'montant_net' => 'nullable|numeric|min:0',
            'montant_paye' => 'nullable|numeric|min:0',
            'date_operation' => 'required|date',
            'date_paiement' => 'nullable|date',
            'statut' => 'nullable|in:calculee,due,payee,annulee',
            'description' => 'nullable|string',
        ]);
        $v['client_id'] = $clientId; $v['created_by'] = Auth::id();
        $v['montant_commission'] ??= ($v['montant_base'] ?? 0) * ($v['taux_commission'] ?? 0) / 100;
        $v['montant_net'] ??= ($v['montant_commission'] ?? 0) - ($v['tva'] ?? 0);
        $v['solde'] = ($v['montant_net'] ?? 0) - ($v['montant_paye'] ?? 0);
        $item = AccountingCommission::create($v);
        return response()->json(['message' => 'Commission créée.', 'item' => $item], 201);
    }

    public function deleteCommission($id)
    {
        $item = AccountingCommission::where('client_id', $this->getClientId())->findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Commission supprimée.']);
    }

    // ═══════════════════════════════════════════════════════════════
    // MOBILE MONEY
    // ═══════════════════════════════════════════════════════════════

    public function mobileTransactions(Request $request)
    {
        $clientId = $this->getClientId();
        $items = AccountingMobileTransaction::where('client_id', $clientId)
            ->orderBy('created_at', 'desc')->take(200)->get()->map(fn($e) => [
                'id' => $e->id, 'reference_transaction' => $e->reference_transaction,
                'operateur' => $e->operateur, 'type' => $e->type,
                'numero_expediteur' => $e->numero_expediteur, 'nom_expediteur' => $e->nom_expediteur,
                'numero_destinataire' => $e->numero_destinataire, 'nom_destinataire' => $e->nom_destinataire,
                'montant' => (float) $e->montant, 'frais' => (float) $e->frais,
                'montant_net' => (float) $e->montant_net,
                'date_transaction' => $e->date_transaction?->format('Y-m-d H:i'),
                'statut' => $e->statut, 'motif' => $e->motif,
            ]);
        return response()->json($items);
    }

    public function storeMobileTransaction(Request $request)
    {
        $clientId = $this->getClientId();
        $v = $request->validate([
            'reference_transaction' => 'nullable|string|max:100',
            'operateur' => 'required|in:mtn,orange,moov,celpaid,andere',
            'type' => 'required|in:depot,retrait,transfert,paiement,remboursement',
            'numero_expediteur' => 'nullable|string|max:20',
            'numero_destinataire' => 'nullable|string|max:20',
            'nom_expediteur' => 'nullable|string|max:255',
            'nom_destinataire' => 'nullable|string|max:255',
            'montant' => 'required|numeric|min:0',
            'frais' => 'nullable|numeric|min:0',
            'solde_avant' => 'nullable|numeric',
            'solde_apres' => 'nullable|numeric',
            'date_transaction' => 'required|date',
            'statut' => 'nullable|in:effectuee,en_attente,echouee,remboursee',
            'motif' => 'nullable|string',
        ]);
        $v['client_id'] = $clientId; $v['created_by'] = Auth::id();
        $v['montant_net'] = ($v['montant'] ?? 0) - ($v['frais'] ?? 0);
        $item = AccountingMobileTransaction::create($v);
        return response()->json(['message' => 'Transaction Mobile Money créée.', 'item' => $item], 201);
    }

    public function deleteMobileTransaction($id)
    {
        $item = AccountingMobileTransaction::where('client_id', $this->getClientId())->findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Transaction supprimée.']);
    }

    // ═══════════════════════════════════════════════════════════════
    // VUES — Facturation (liste des écritures type vente)
    // ═══════════════════════════════════════════════════════════════

    public function invoices(Request $request)
    {
        $clientId = $this->getClientId();
        $items = \App\Models\AccountingJournal::where('client_id', $clientId)
            ->whereIn('journal_type', ['vente', 'achat'])
            ->orderBy('created_at', 'desc')->take(200)->get()->map(fn($e) => [
                'id' => $e->id, 'reference' => $e->reference, 'journal_type' => $e->journal_type,
                'entry_date' => $e->entry_date?->format('Y-m-d'), 'description' => $e->description,
                'debit_total' => (float) $e->debit_total, 'credit_total' => (float) $e->credit_total,
                'status' => $e->status, 'numero_piece' => $e->numero_piece,
                'client_name' => $e->client_name,
            ]);
        return response()->json($items);
    }

    // ═══════════════════════════════════════════════════════════════
    // VUES — Taxe de séjour (filtre hotel_factures.taxe_sejour > 0)
    // ═══════════════════════════════════════════════════════════════

    public function taxeNuitees(Request $request)
    {
        $clientId = $this->getClientId();
        $items = AccountingHotelFacture::where('client_id', $clientId)
            ->where('taxe_sejour', '>', 0)
            ->orderBy('created_at', 'desc')->take(200)->get()->map(fn($e) => [
                'id' => $e->id, 'numero_facture' => $e->numero_facture,
                'client_nom' => $e->client_nom, 'chambre' => $e->chambre,
                'date_arrivee' => $e->date_arrivee?->format('Y-m-d'),
                'date_depart' => $e->date_depart?->format('Y-m-d'),
                'nb_nuitees' => $e->nb_nuitees,
                'taxe_sejour' => (float) $e->taxe_sejour,
                'montant_ttc' => (float) $e->montant_ttc, 'statut' => $e->statut,
            ]);
        return response()->json($items);
    }

    // ═══════════════════════════════════════════════════════════════
    // VUES — Loyers impayés (quittances solde > 0)
    // ═══════════════════════════════════════════════════════════════

    public function loyersImpayes(Request $request)
    {
        $clientId = $this->getClientId();
        $items = AccountingQuittance::where('client_id', $clientId)
            ->where('solde', '>', 0)
            ->orderBy('solde', 'desc')->get()->map(fn($e) => [
                'id' => $e->id, 'numero_quittance' => $e->numero_quittance,
                'bien' => $e->bien, 'locataire_nom' => $e->locataire_nom,
                'periode' => $e->periode, 'loyer_ht' => (float) $e->loyer_ht,
                'montant_total' => (float) $e->montant_total,
                'montant_paye' => (float) $e->montant_paye, 'solde' => (float) $e->solde,
                'statut' => $e->statut, 'date_echeance' => $e->date_echeance?->format('Y-m-d'),
            ]);
        return response()->json($items);
    }

    // ═══════════════════════════════════════════════════════════════
    // VUES — Trésorerie
    // ═══════════════════════════════════════════════════════════════

    public function treasury(Request $request)
    {
        $clientId = $this->getClientId();
        $totalDebit = \App\Models\AccountingJournal::where('client_id', $clientId)
            ->where('status', 'posted')->sum('debit_total');
        $totalCredit = \App\Models\AccountingJournal::where('client_id', $clientId)
            ->where('status', 'posted')->sum('credit_total');
        $recent = \App\Models\AccountingJournal::where('client_id', $clientId)
            ->where('status', 'posted')
            ->orderBy('entry_date', 'desc')->take(50)->get()->map(fn($e) => [
                'id' => $e->id, 'reference' => $e->reference, 'journal_type' => $e->journal_type,
                'entry_date' => $e->entry_date?->format('Y-m-d'),
                'description' => $e->description,
                'debit_total' => (float) $e->debit_total, 'credit_total' => (float) $e->credit_total,
            ]);
        return response()->json([
            'solde' => $totalDebit - $totalCredit,
            'total_entrees' => $totalDebit,
            'total_sorties' => $totalCredit,
            'recent' => $recent,
        ]);
    }
}
