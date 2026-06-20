<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItKnowledgeBase extends Model
{
    protected $table = 'it_knowledge_base';

    protected $fillable = [
        'title', 'slug', 'category', 'content', 'tags',
        'is_public', 'views', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'json',
            'is_public' => 'boolean',
        ];
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
