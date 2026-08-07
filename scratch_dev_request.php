<?php
$c = App\Models\Client::first();
App\Models\Gel\ItDevRequest::create([
    'subject' => 'Refonte Site Web',
    'description' => 'Besoin de refonte',
    'client_id' => $c ? $c->id : null,
    'author_id' => App\Models\User::first()->id,
    'status' => 'recue'
]);
echo "done";
