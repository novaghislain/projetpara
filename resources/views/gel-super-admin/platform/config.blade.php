@extends('layouts.gel-super-admin')

@section('title', 'Configuration de la Plateforme - Super Admin GEL')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-white fw-bold">Configuration de la Plateforme</h1>
            <p class="text-muted mb-0">Gérez les fonctionnalités (Feature Flags) et les quotas globaux</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success bg-success text-white border-0 alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm border-0 mb-4" style="background: var(--gel-card-bg);">
        <div class="card-body p-4">
            <form action="{{ route('gel-super-admin.platform.config.update') }}" method="POST">
                @csrf
                @method('PUT')

                <h5 class="text-white mb-4 border-bottom border-secondary pb-2">
                    <i class="fas fa-toggle-on text-primary me-2"></i> Feature Flags
                </h5>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="card border border-secondary" style="background: transparent;">
                            <div class="card-body">
                                <div class="form-check form-switch d-flex align-items-center justify-content-between p-0">
                                    <label class="form-check-label text-white mb-0" for="enable_ia">
                                        <strong>Assistant IA</strong><br>
                                        <small class="text-muted">Activer l'assistant IA (Claude/Momo) pour l'ensemble des cabinets</small>
                                    </label>
                                    <input type="hidden" name="settings[enable_ia][type]" value="boolean">
                                    <input type="hidden" name="settings[enable_ia][description]" value="Activer l'assistant IA globalement">
                                    <input type="hidden" name="settings[enable_ia][value]" value="0">
                                    <input class="form-check-input ms-3" type="checkbox" role="switch" id="enable_ia" name="settings[enable_ia][value]" value="1" 
                                           {{ ($settings['enable_ia']->value ?? '0') ? 'checked' : '' }} style="width: 3em; height: 1.5em;">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="card border border-secondary" style="background: transparent;">
                            <div class="card-body">
                                <div class="form-check form-switch d-flex align-items-center justify-content-between p-0">
                                    <label class="form-check-label text-white mb-0" for="enable_whatsapp">
                                        <strong>Intégration WhatsApp</strong><br>
                                        <small class="text-muted">Activer les notifications WhatsApp globalement</small>
                                    </label>
                                    <input type="hidden" name="settings[enable_whatsapp][type]" value="boolean">
                                    <input type="hidden" name="settings[enable_whatsapp][description]" value="Activer les notifications WhatsApp">
                                    <input type="hidden" name="settings[enable_whatsapp][value]" value="0">
                                    <input class="form-check-input ms-3" type="checkbox" role="switch" id="enable_whatsapp" name="settings[enable_whatsapp][value]" value="1" 
                                           {{ ($settings['enable_whatsapp']->value ?? '0') ? 'checked' : '' }} style="width: 3em; height: 1.5em;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <h5 class="text-white mb-4 border-bottom border-secondary pb-2 mt-5">
                    <i class="fas fa-server text-warning me-2"></i> Quotas et Limites
                </h5>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-white">Quota IA par défaut (Requêtes/mois)</label>
                        <input type="hidden" name="settings[default_ia_quota][type]" value="integer">
                        <input type="hidden" name="settings[default_ia_quota][description]" value="Quota IA par défaut pour les nouveaux cabinets">
                        <input type="number" class="form-control" name="settings[default_ia_quota][value]" 
                               value="{{ $settings['default_ia_quota']->value ?? '100' }}"
                               style="background-color: rgba(255,255,255,0.05); color: white; border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-white">Délai de rétention des sauvegardes (Jours)</label>
                        <input type="hidden" name="settings[backup_retention_days][type]" value="integer">
                        <input type="hidden" name="settings[backup_retention_days][description]" value="Nombre de jours de conservation des sauvegardes">
                        <input type="number" class="form-control" name="settings[backup_retention_days][value]" 
                               value="{{ $settings['backup_retention_days']->value ?? '30' }}"
                               style="background-color: rgba(255,255,255,0.05); color: white; border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12 mb-3">
                        <label class="form-label text-white">URL du Calendrier Fiscal partagé (iCal / Google Calendar)</label>
                        <input type="hidden" name="settings[tax_calendar_url][type]" value="string">
                        <input type="hidden" name="settings[tax_calendar_url][description]" value="URL publique du calendrier fiscal pour tous les cabinets">
                        <input type="url" class="form-control" name="settings[tax_calendar_url][value]" 
                               value="{{ $settings['tax_calendar_url']->value ?? '' }}"
                               placeholder="https://calendar.google.com/calendar/ical/..."
                               style="background-color: rgba(255,255,255,0.05); color: white; border: 1px solid rgba(255,255,255,0.1);">
                        <small class="text-muted">Lien affiché et synchronisé sur les tableaux de bord des cabinets.</small>
                    </div>
                </div>

                <h5 class="text-white mb-4 border-bottom border-secondary pb-2 mt-5">
                    <i class="fas fa-globe text-info me-2"></i> Maintenance
                </h5>

                <div class="row mb-4">
                    <div class="col-md-12 mb-3">
                        <div class="card border border-danger" style="background: transparent;">
                            <div class="card-body">
                                <div class="form-check form-switch d-flex align-items-center justify-content-between p-0">
                                    <label class="form-check-label text-white mb-0" for="maintenance_mode">
                                        <strong class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i> Mode Maintenance Global</strong><br>
                                        <small class="text-muted">Si activé, tous les cabinets verront une page de maintenance. Vous seul aurez accès à l'interface.</small>
                                    </label>
                                    <input type="hidden" name="settings[maintenance_mode][type]" value="boolean">
                                    <input type="hidden" name="settings[maintenance_mode][description]" value="Mode maintenance global">
                                    <input type="hidden" name="settings[maintenance_mode][value]" value="0">
                                    <input class="form-check-input ms-3 bg-danger" type="checkbox" role="switch" id="maintenance_mode" name="settings[maintenance_mode][value]" value="1" 
                                           {{ ($settings['maintenance_mode']->value ?? '0') ? 'checked' : '' }} style="width: 3em; height: 1.5em; border-color: #dc3545;">
                                </div>
                                <div class="mt-3">
                                    <label class="form-label text-white">Message de maintenance</label>
                                    <input type="hidden" name="settings[maintenance_message][type]" value="string">
                                    <input type="hidden" name="settings[maintenance_message][description]" value="Message affiché pendant la maintenance">
                                    <textarea class="form-control" name="settings[maintenance_message][value]" rows="2"
                                              style="background-color: rgba(255,255,255,0.05); color: white; border: 1px solid rgba(255,255,255,0.1);"
                                              placeholder="Nous effectuons actuellement une maintenance.">{{ $settings['maintenance_message']->value ?? 'Nous effectuons actuellement une maintenance. Veuillez réessayer plus tard.' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4 pt-3 border-top border-secondary">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i> Sauvegarder la configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
