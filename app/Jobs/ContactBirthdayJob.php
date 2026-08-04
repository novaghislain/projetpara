<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\ClientContact;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ContactBirthdayJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // 1. Trouver les contacts dont c'est l'anniversaire demain
        // On suppose que ClientContact a un champ date_anniversaire
        // Si le champ n'existe pas, on ignorera gracieusement.
        
        if (!\Illuminate\Support\Facades\Schema::hasColumn('client_contacts', 'date_anniversaire')) {
            return;
        }

        $tomorrow = now()->addDay();

        $contacts = ClientContact::whereMonth('date_anniversaire', $tomorrow->month)
            ->whereDay('date_anniversaire', $tomorrow->day)
            ->with(['client'])
            ->get();

        foreach ($contacts as $contact) {
            $clientId = $contact->client_id;
            $cabinetId = $contact->client->cabinet_id ?? null;
            
            if (!$cabinetId) continue;
            
            // Trouver le responsable du client ou les secrétaires
            $users = User::where('cabinet_id', $cabinetId)
                ->where(function($q) use ($clientId) {
                    $q->where('client_id', $clientId)
                      ->orWhereNull('client_id');
                })
                ->get();
                
            foreach ($users as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'info',
                    'title' => 'Rappel : Anniversaire demain !',
                    'message' => "N'oubliez pas, c'est l'anniversaire de {$contact->nom} ({$contact->client->company_name}) demain le " . $tomorrow->format('d/m') . ".",
                    'data' => ['url' => route('gel-secretary.contacts.index')]
                ]);
            }
        }
    }
}
