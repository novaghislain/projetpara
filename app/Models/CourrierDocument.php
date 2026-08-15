<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourrierDocument extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'courrier_documents';

    protected $fillable = [
        'courrier_id',
        'document_id'
    ];

}
