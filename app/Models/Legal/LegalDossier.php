<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class LegalDossier extends LegalBaseModel
{
    use HasFactory;

    protected $guarded = ['id'];
}