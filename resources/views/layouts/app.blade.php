@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>@yield('page-title')</h1>
@stop

@section('content')
    @yield('main-content')
@stop