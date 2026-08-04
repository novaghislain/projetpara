{{-- ================================================================ --}}
{{-- === resources/views/partials/_form-errors.blade.php           === --}}
{{-- === Partial — Affichage des erreurs de validation             === --}}
{{-- ================================================================ --}}

@if($errors->any())
<div class="gel-alert gel-alert-danger" role="alert">
    <div class="gel-alert-icon"><i class="fas fa-exclamation-circle"></i></div>
    <div class="gel-alert-content">
        <strong>{{ __('Validation échouée') }}</strong>
        <ul class="gel-alert-list">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    <button type="button" class="gel-alert-close" onclick="this.closest('.gel-alert').remove()">&times;</button>
</div>
@endif

