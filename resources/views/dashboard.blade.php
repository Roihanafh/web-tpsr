@extends('layouts.app')

@section('css')
    @parent
    <style>
        .content-header {
            display: none !important;
        }
    </style>
@stop

{{-- Override content header to hide the default page title --}}
@section('content_header')
@endsection

@section('main-content')
    @if ($isAdmin)
        @include('admin.dashboard')
    @else
        @include('guru.dashboard')
    @endif

    {{-- Modal Buku Panduan --}}
    @include('panduan.modal')
@endsection