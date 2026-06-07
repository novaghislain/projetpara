@extends('app')

@section('title', 'Boutique - Victoire Para')

@section('content')
    <Shop :products="{{ $products }}"></Shop>
@endsection
