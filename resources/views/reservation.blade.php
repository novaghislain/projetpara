@extends('app')

@section('title', 'Réservation - Victoire Para')

@section('content')
    <Reservation :product="{{ json_encode($product) }}" :user="{{ $user ? json_encode(['id' => $user->id, 'name' => $user->name, 'email' => $user->email]) : 'null' }}"></Reservation>
@endsection
