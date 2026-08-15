<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'tasks';

    protected $fillable = [
        'cabinet_id',
        'client_id',
        'assigned_to',
        'created_by',
        'titre',
        'description',
        'priorite',
        'statut',
        'date_echeance',
        'termine_at',
        'source',
        'coordination_type',
        'related_document_id'
    ];

}
