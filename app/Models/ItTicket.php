<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItTicket extends Model
{
    protected $fillable = [
        'client_id', 'ticket_number', 'title', 'description',
        'type', 'priority', 'status', 'category',
        'assigned_to', 'requested_by', 'sla_id', 'sla_due_at',
        'sla_breached', 'resolution', 'first_response_at',
        'resolved_at', 'closed_at', 'billable', 'billed_hours',
    ];

    protected function casts(): array
    {
        return [
            'sla_breached' => 'boolean',
            'billable' => 'boolean',
            'sla_due_at' => 'datetime',
            'first_response_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'billed_hours' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function requestedBy(): BelongsTo { return $this->belongsTo(User::class, 'requested_by'); }
    public function sla(): BelongsTo { return $this->belongsTo(ItSlaPolicy::class, 'sla_id'); }
    public function comments(): HasMany { return $this->hasMany(ItTicketComment::class, 'ticket_id'); }
}
