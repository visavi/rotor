@extends('layout_simple')

@section('title', 'BBCode')

@section('content')
    {{ bbCode($message) }}
@stop
