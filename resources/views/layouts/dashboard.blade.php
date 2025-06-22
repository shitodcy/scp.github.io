@extends('layouts.admin')

@section('title', 'Monitoring Website')

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Status Umum Website</h3>
    </div>
    <div class="card-body">
        <p>Data monitoring akan ditampilkan di sini.</p>
        <p>Terakhir diperbarui: <strong>{{ now()->format('d M Y, H:i:s') }}</strong></p>
        
        <div class="row mt-4">
            {{-- Box Pengunjung Hari Ini (Data Statis dari Screenshot) --}}
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="far fa-envelope"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Pengunjung Hari Ini</span>
                        <span class="info-box-number">1,410</span>
                    </div>
                </div>
            </div>

            {{-- Box Status Database (Data Dinamis dari Controller) --}}
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box {{ $dbStatus === 'Online' ? 'bg-success' : 'bg-danger' }}">
                    <span class="info-box-icon"><i class="fas fa-database"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Status Database</span>
                        <span class="info-box-number">{{ $dbStatus }}</span>
                    </div>
                </div>
            </div>

            {{-- Box Total User (Data Dinamis dari Controller) --}}
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box bg-primary">
                    <span class="info-box-icon"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total User Terdaftar</span>
                        <span class="info-box-number">{{ $userCount }}</span>
                    </div>
                </div>
            </div>

            {{-- Box Load Server (Data Statis dari Screenshot) --}}
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box bg-warning">
                    <span class="info-box-icon"><i class="fas fa-server"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Load Server</span>
                        <span class="info-box-number">0.52 <small>(Normal)</small></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
