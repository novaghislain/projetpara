<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_name',
        'legal_form',
        'rccm',
        'ifu',
        'address',
        'city',
        'country',
        'phone',
        'email',
        'website',
        'status',
        'contract_type',
        'contract_start',
        'contract_end',
        'notes',
        'disabled_modules',
        'require_2fa',
        'session_timeout_minutes',
        'allowed_ips',
        'regime_fiscal',
        'emecef_nim',
        'emecef_is_active',
        'emecef_password',
        'created_by',
        'domain_code',
        'domain_id',
        'domain_confirmed',
        'domain_confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'contract_start' => 'date',
            'contract_end' => 'date',
            'disabled_modules' => 'array',
            'require_2fa' => 'boolean',
            'emecef_is_active' => 'boolean',
            'emecef_password' => 'encrypted',
            'allowed_ips' => 'json',
            'domain_confirmed' => 'boolean',
            'domain_confirmed_at' => 'datetime',
        ];
    }

    public function domain()
    {
        return $this->belongsTo(BusinessDomain::class, 'domain_id');
    }

    public function accountingModules()
    {
        return $this->hasMany(ClientAccountingModule::class, 'client_id');
    }

    public function activeAccountingModules()
    {
        return $this->hasMany(ClientAccountingModule::class, 'client_id')->where('is_active', true);
    }

    public function contacts()
    {
        return $this->hasMany(ClientContact::class);
    }

    public function primaryContact()
    {
        return $this->hasOne(ClientContact::class)->where('is_primary', true);
    }

    public function poles()
    {
        return $this->belongsToMany(Pole::class, 'client_pole')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function activePoles()
    {
        return $this->belongsToMany(Pole::class, 'client_pole')
            ->wherePivot('is_active', true)
            ->withTimestamps();
    }

    public function missions()
    {
        return $this->hasMany(Mission::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'client_service')
            ->withPivot(['status', 'start_date', 'end_date', 'settings'])
            ->withTimestamps();
    }

    public function folders()
    {
        return $this->hasMany(ClientFolder::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function licenses()
    {
        return $this->hasMany(License::class);
    }

    public function activeLicenses()
    {
        return $this->hasMany(License::class)->where('status', 'active');
    }

    public function companyAdmins()
    {
        return $this->hasMany(User::class)->where('is_company_admin', true);
    }
}
