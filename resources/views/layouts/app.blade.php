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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                        maintainAspectRatio: false,
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
                        if (!window.jspdf || !window.jspdf.jsPDF) {
                            alert('Library PDF tidak termuat dengan benar.');
                            return;
                        }

                        const { jsPDF } = window.jspdf;
                        const doc = new jsPDF({
                            orientation: 'portrait',
                            unit: 'mm',
                            format: 'a4'
                        });

                        // 1. Judul Laporan
                        doc.setFont('helvetica', 'bold');
                        doc.setFontSize(18);
                        doc.setTextColor(0, 0, 0);
                        doc.text('Laporan Analisis Individu', 105, 22, { align: 'center' });

                        // Garis pemisah
                        doc.setDrawColor(0, 0, 0);
                        doc.setLineWidth(0.6);
                        doc.line(20, 27, 190, 27);

                        // 2. Info Siswa (menggunakan autotable tanpa border)
                        var name = (document.getElementById('pdf-siswa-nama')?.textContent || '-').trim();
                        var kelasVal = (document.getElementById('pdf-siswa-kelas')?.textContent || '-').trim();
                        var pengajar = (document.getElementById('pdf-siswa-pengajar')?.textContent || '-').trim();
                        var sekolah = (document.getElementById('pdf-siswa-sekolah')?.textContent || '-').trim();
                        var tahunAjar = (document.getElementById('pdf-siswa-tahun-ajar')?.textContent || '-').trim();

                        doc.autoTable({
                            startY: 32,
                            margin: { left: 20, right: 20 },
                            theme: 'plain',
                            styles: {
                                fontSize: 10,
                                cellPadding: 1.5,
                                font: 'helvetica',
                                textColor: [0, 0, 0]
                            },
                            columnStyles: {
                                0: { fontStyle: 'bold', width: 30 },
                                1: { width: 5 },
                                2: { fontStyle: 'normal' }
                            },
                            body: [
                                ['Nama', ':', name],
                                ['Kelas', ':', kelasVal],
                                ['Pengajar', ':', pengajar],
                                ['Sekolah', ':', sekolah],
                                ['Tahun Ajar', ':', tahunAjar]
                            ]
                        });

                        var currentY = doc.lastAutoTable.finalY + 8;

                        // 3. Tambahkan Grafik
                        if (canvas) {
                            try {
                                var chartImgData = canvas.toDataURL('image/png');
                                // A4 lebar 210mm. Margin kiri-kanan 20mm -> printable 170mm.
                                // Lebar chart diperbesar menjadi 160mm, tinggi menjadi 80mm agar skala lebih terlihat.
                                // Center X: (210 - 160) / 2 = 25mm
                                doc.addImage(chartImgData, 'PNG', 25, currentY, 160, 80);
                                
                                // Label grafik
                                doc.setFont('helvetica', 'italic');
                                doc.setFontSize(9);
                                doc.setTextColor(80, 80, 80);
                                doc.text('Grafik Penilaian', 105, currentY + 85, { align: 'center' });
                                
                                currentY += 93;
                            } catch (e) {
                                console.error('Gagal mengambil data chart:', e);
                                currentY += 5;
                            }
                        }

                        // 4. Tabel Jumlah Perolehan
                        doc.setFont('helvetica', 'bold');
                        doc.setFontSize(11);
                        doc.setTextColor(0, 0, 0);
                        doc.text('Jumlah Perolehan', 20, currentY);

                        var levelCounts = [];
                        for (var i = 0; i <= 5; i++) {
                            var el = document.getElementById('pdf-siswa-lvl-' + i);
                            levelCounts.push(el ? el.textContent.trim() : '0');
                        }

                        doc.autoTable({
                            startY: currentY + 3,
                            margin: { left: 20, right: 20 },
                            theme: 'grid',
                            headStyles: {
                                fillColor: [243, 244, 246],
                                textColor: [0, 0, 0],
                                lineColor: [100, 100, 100],
                                lineWidth: 0.15,
                                fontStyle: 'bold',
                                halign: 'center'
                            },
                            bodyStyles: {
                                textColor: [0, 0, 0],
                                lineColor: [100, 100, 100],
                                lineWidth: 0.15,
                                halign: 'center'
                            },
                            styles: {
                                fontSize: 9,
                                font: 'helvetica',
                                cellPadding: 3
                            },
                            head: [['L0', 'L1', 'L2', 'L3', 'L4', 'L5']],
                            body: [levelCounts]
                        });

                        // 5. Rata-rata dan Status
                        var rata = (document.getElementById('pdf-siswa-rata')?.textContent || '0.00').trim();
                        var statusVal = (document.getElementById('pdf-siswa-status')?.textContent || '-').trim();

                        doc.autoTable({
                            startY: doc.lastAutoTable.finalY + 8,
                            margin: { left: 20, right: 20 },
                            theme: 'plain',
                            styles: {
                                fontSize: 10,
                                cellPadding: 1.5,
                                font: 'helvetica',
                                textColor: [0, 0, 0]
                            },
                            columnStyles: {
                                0: { fontStyle: 'bold', width: 35 },
                                1: { width: 5 },
                                2: { fontStyle: 'normal' }
                            },
                            body: [
                                ['Rata-rata Point', ':', rata],
                                ['Status', ':', statusVal]
                            ]
                        });

                        doc.save('laporan-' + slug + '.pdf');
                    });
                }
            }

            doInit();
        });
    </script>
@stop
