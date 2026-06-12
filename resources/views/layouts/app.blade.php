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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    @vite('resources/js/app.js')
    @livewireScripts
    @stack('scripts')

    <script>
        window.addEventListener('init-siswa-chart', function (e) {
            var labels = e.detail.labels;
            var values = e.detail.values;
            var nama   = e.detail.nama;
            var slug   = e.detail.slug;

            // Tunggu canvas tersedia di DOM
            function doInit() {
                var canvas = document.getElementById('siswaChart');
                if (!canvas) { setTimeout(doInit, 50); return; }

                if (window._siswaChartInstance) {
                    window._siswaChartInstance.destroy();
                    window._siswaChartInstance = null;
                }

                window._siswaChartInstance = new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: nama,
                            data: values,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59,130,246,0.08)',
                            pointBackgroundColor: '#3b82f6',
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            borderWidth: 2.5,
                            tension: 0.3,
                            spanGaps: true,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: true, position: 'top' },
                            tooltip: {
                                callbacks: {
                                    label: function (c) { return 'Level: L' + c.parsed.y; }
                                }
                            }
                        },
                        scales: {
                            x: { title: { display: true, text: 'Pertemuan' } },
                            y: {
                                min: 0, max: 5,
                                ticks: { stepSize: 1, callback: function (v) { return 'L' + v; } },
                                title: { display: true, text: 'Level' }
                            }
                        }
                    }
                });

                var btn = document.getElementById('btnDownloadChart');
                if (btn) {
                    btn.onclick = function () {
                        var link = document.createElement('a');
                        link.download = 'grafik-' + slug + '.png';
                        link.href = canvas.toDataURL('image/png');
                        link.click();
                    };
                }
            }

            doInit();
        });
    </script>
@stop
