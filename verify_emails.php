<?php
use App\Models\User;

User::query()->update(['email_verified_at' => now()]);
echo "Emails verified successfully.\n";
