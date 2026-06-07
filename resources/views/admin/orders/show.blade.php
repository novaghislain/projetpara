@extends('app')

@section('content')
    <Order-Detail :order="{{ $order }}"></Order-Detail>
@endsection
