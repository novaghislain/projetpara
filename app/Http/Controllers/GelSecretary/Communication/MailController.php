<?php

namespace App\Http\Controllers\GelSecretary\Communication;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
use App\Models\ClientEmailConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Webklex\IMAP\Facades\Client as ImapClient;

class MailController extends Controller
{
    /**
     * Affiche l'interface du Webmail (Boîte Mail externe).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $clients = Client::orderBy('nom_entreprise')->get();
        
        $activeClientId = session('active_client_id');
        $activeClient = $activeClientId ? Client::find($activeClientId) : null;

        $emailConfig = null;
        $messages = [];
        $folders = [];
        $imapError = null;
        $currentFolder = $request->query('folder', 'INBOX');

        if ($activeClient) {
            $emailConfig = ClientEmailConfig::where('client_id', $activeClient->id)->first();

            if ($emailConfig && $emailConfig->imap_host) {
                try {
                    $client = ImapClient::make([
                        'host'          => $emailConfig->imap_host,
                        'port'          => $emailConfig->imap_port,
                        'encryption'    => $emailConfig->imap_encryption,
                        'validate_cert' => false,
                        'username'      => $emailConfig->imap_username,
                        'password'      => $emailConfig->imap_password,
                        'protocol'      => 'imap'
                    ]);

                    $client->connect();
                    
                    // Fetch all folders for the sidebar
                    $folders = $client->getFolders();

                    // Get messages for the selected folder
                    $folder = $client->getFolder($currentFolder);
                    if ($folder) {
                        $messages = $folder->messages()->all()->limit(15)->get();
                    }

                } catch (\Exception $e) {
                    $imapError = "Impossible de se connecter à la boîte mail: " . $e->getMessage();
                }
            }
        }

        return view('gel-secretary.mail.index', compact('clients', 'activeClient', 'emailConfig', 'messages', 'folders', 'currentFolder', 'imapError'));
    }

    /**
     * Sauvegarde la configuration email pour le client actif.
     */
    public function saveConfig(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'imap_host' => 'required|string',
            'imap_port' => 'required|integer',
            'imap_username' => 'required|string',
            'imap_password' => 'required|string',
            'smtp_host' => 'nullable|string',
            'smtp_port' => 'nullable|integer',
        ]);

        ClientEmailConfig::updateOrCreate(
            ['client_id' => $request->client_id],
            [
                'imap_host' => $request->imap_host,
                'imap_port' => $request->imap_port,
                'imap_encryption' => $request->imap_encryption ?? 'ssl',
                'imap_username' => $request->imap_username,
                'imap_password' => $request->imap_password,
                'smtp_host' => $request->smtp_host,
                'smtp_port' => $request->smtp_port ?? 465,
                'smtp_encryption' => $request->smtp_encryption ?? 'ssl',
                'smtp_username' => $request->smtp_username ?? $request->imap_username,
                'smtp_password' => $request->smtp_password ?? $request->imap_password,
            ]
        );

        return back()->with('success', 'Configuration de la boîte mail sauvegardée avec succès.');
    }

    /**
     * Lit le contenu complet d'un e-mail.
     */
    public function show(Request $request, $uid)
    {
        $activeClientId = session('active_client_id');
        $activeClient = $activeClientId ? Client::find($activeClientId) : null;
        if (!$activeClient) return response()->json(['error' => 'Client non sélectionné'], 400);

        $emailConfig = ClientEmailConfig::where('client_id', $activeClient->id)->first();
        if (!$emailConfig || !$emailConfig->imap_host) return response()->json(['error' => 'IMAP non configuré'], 400);

        try {
            $client = ImapClient::make([
                'host'          => $emailConfig->imap_host,
                'port'          => $emailConfig->imap_port,
                'encryption'    => $emailConfig->imap_encryption,
                'validate_cert' => false,
                'username'      => $emailConfig->imap_username,
                'password'      => $emailConfig->imap_password,
                'protocol'      => 'imap'
            ]);
            $client->connect();
            
            $folderName = $request->query('folder', 'INBOX');
            $folder = $client->getFolder($folderName);
            $message = $folder->query()->getMessageByUid($uid);

            if ($message) {
                $message->setFlag(['\Seen']); // Mark as read
                
                return response()->json([
                    'subject' => $message->getSubject(),
                    'from' => $message->getFrom()[0]->mail ?? '',
                    'date' => $message->getDate()->format('d/m/Y H:i'),
                    'body' => $message->hasHTMLBody() ? $message->getHTMLBody() : nl2br($message->getTextBody())
                ]);
            }
            return response()->json(['error' => 'Message non trouvé'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Envoie un e-mail en utilisant le SMTP du client.
     */
    public function send(Request $request)
    {
        $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string'
        ]);

        $activeClientId = session('active_client_id');
        $activeClient = $activeClientId ? Client::find($activeClientId) : null;
        if (!$activeClient) return back()->with('error', 'Client non sélectionné.');

        $emailConfig = ClientEmailConfig::where('client_id', $activeClient->id)->first();
        if (!$emailConfig || !$emailConfig->smtp_host) return back()->with('error', 'SMTP non configuré.');

        try {
            // Configuration dynamique SMTP
            config([
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => $emailConfig->smtp_host,
                'mail.mailers.smtp.port' => $emailConfig->smtp_port,
                'mail.mailers.smtp.encryption' => $emailConfig->smtp_encryption,
                'mail.mailers.smtp.username' => $emailConfig->smtp_username,
                'mail.mailers.smtp.password' => $emailConfig->smtp_password,
                'mail.from.address' => $emailConfig->smtp_username,
                'mail.from.name' => $activeClient->nom_entreprise,
            ]);

            \Illuminate\Support\Facades\Mail::raw($request->message, function ($mail) use ($request) {
                $mail->to($request->to)
                     ->subject($request->subject);
            });

            return back()->with('success', 'Email envoyé avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur d\'envoi: ' . $e->getMessage());
        }
    }
}


