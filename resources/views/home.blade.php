@extends('app')

@section('content')
    <Home :best-sellers="{{ $bestSellers }}"></Home>
@endsection
