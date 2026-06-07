@extends('app')

@section('content')
    <Order-Index :orders="{{ $orders }}"></Order-Index>
@endsection
