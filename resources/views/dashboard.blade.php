@extends('layouts.app')

@section('page-title')
Dashboard
@endsection

@section('main-content')

<div class="row">

    <div class="col-md-3">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>100</h3>
                <p>Total User</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>25</h3>
                <p>Data Aktif</p>
            </div>
        </div>
    </div>

</div>

@endsection