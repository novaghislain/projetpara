@extends('app')

@section('title', 'Paiement - Victoire Para')

@section('content')
    <Checkout product-id="{{ request('id') }}"></Checkout>
@endsection
