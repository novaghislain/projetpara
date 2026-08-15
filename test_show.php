<?php
$user = \App\Models\User::first();
\Illuminate\Support\Facades\Auth::login($user);
echo app()->handle(Illuminate\Http\Request::create('/gel-secretary/courriers/1', 'GET'))->getContent();
