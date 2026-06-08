@extends('adminlte::page')

@section('title', 'Dashboard')

@section('css')
    @livewireStyles
    @vite('resources/css/app.css')
@stop

@section('content_header')
    <h1>@yield('page-title')</h1>
@stop

@section('content')
    @yield('main-content')
@stop

@section('js')
    @vite('resources/js/app.js')
    @livewireScripts
@stop
