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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    @vite('resources/js/app.js')
    @livewireScripts
    @stack('scripts')

    <script>
        window.addEventListener('init-siswa-chart', function (e) {
            var labels = e.detail.labels;
            var values = e.detail.values;
            var nama   = e.detail.nama;
            var slug   = e.detail.slug;

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
                        animation: {
                            duration: 600,
                            onComplete: function() {
                                // Setelah animasi selesai, salin chart ke img PDF template
                                var imgEl = document.getElementById('laporan-pdf-chart-img');
                                if (imgEl && canvas) {
                                    imgEl.src = canvas.toDataURL('image/png');
                                }
                            }
                        },
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

                // Tombol Download PDF
                var btn = document.getElementById('btnDownloadChart');
                if (btn) {
                    // Hapus listener lama agar tidak dobel
                    btn.replaceWith(btn.cloneNode(true));
                    btn = document.getElementById('btnDownloadChart');

                    btn.addEventListener('click', function () {
                        var pdfContent = document.getElementById('laporan-pdf-content');
                        if (!pdfContent) { alert('Template PDF tidak ditemukan.'); return; }

                        // Pastikan gambar chart sudah ter-set
                        var imgEl = document.getElementById('laporan-pdf-chart-img');
                        if (imgEl && canvas) {
                            imgEl.src = canvas.toDataURL('image/png');
                        }

                        // Tampilkan wrapper sementara
                        var wrapper = pdfContent.parentElement;
                        var prevStyle = wrapper.getAttribute('style') || '';
                        wrapper.style.cssText = 'position:fixed; left:0; top:0; z-index:-1; visibility:hidden;';

                        var opt = {
                            margin:      [10, 20, 10, 20],
                            filename:    'laporan-' + slug + '.pdf',
                            image:       { type: 'png', quality: 1 },
                            html2canvas: {
                                scale: 2,
                                useCORS: true,
                                logging: false,
                                allowTaint: true,
                                backgroundColor: '#ffffff'
                            },
                            jsPDF: {
                                unit: 'mm',
                                format: 'a4',
                                orientation: 'portrait'
                            },
                            pagebreak: { mode: 'avoid-all' }
                        };

                        html2pdf()
                            .set(opt)
                            .from(pdfContent)
                            .save()
                            .then(function () {
                                wrapper.setAttribute('style', prevStyle);
                            });
                    });
                }
            }

            doInit();
        });
    </script>
@stop
