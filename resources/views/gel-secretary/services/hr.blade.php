@extends('layouts.gel-secretary')
@section('title', 'Ressources Humaines - Secrétariat')

@section('content')
<div class="pro-header animate-fade">
  <div>
    <div class="pro-title"><i class="fas fa-users" style="color:#0EA5E9; margin-right:8px;"></i> Ressources Humaines</div>
    <div class="pro-subtitle">Suivi des congés, absences et documents RH</div>
  </div>
</div>

<div class="animate-fade delay-1" style="margin-top:24px;" id="app">
    <!-- Le composant Vue sera monté ici -->
    <hr-manager></hr-manager>
</div>
@endsection
