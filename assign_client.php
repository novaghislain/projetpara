<?php
use App\Models\Client;
use App\Models\User;

$client = Client::firstOrCreate(
    ['company_name' => 'Entreprise Test'], 
    ['status' => 'actif', 'email' => 'admin@entreprise.com']
);

User::where('email', 'admin@entreprise.com')->update([
    'client_id' => $client->id, 
    'active_client_id' => $client->id
]);

echo "Client assigned!\n";
