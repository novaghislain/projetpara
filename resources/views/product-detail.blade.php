@extends('app')

@section('title', $product->name . ' - Victoire Para')

@section('content')
    <Product-Detail :product="{{ $product }}"></Product-Detail>
@endsection
