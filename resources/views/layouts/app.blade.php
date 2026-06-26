@extends('adminlte::page')

@section('title', 'Dashboard')

@section('css')
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
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
        // ─── Plugin angka di tiap titik — HANYA untuk export (PDF snapshot) ───
        var pointLabelsPlugin = {
            id: 'pointLabels',
            afterDatasetsDraw: function(chart) {
                // Hanya aktif jika chart sedang dalam mode "forExport"
                if (!chart.config._forExport) return;
                var ctx = chart.ctx;
                chart.data.datasets.forEach(function(dataset, i) {
                    var meta = chart.getDatasetMeta(i);
                    meta.data.forEach(function(point, index) {
                        var val = dataset.data[index];
                        if (val === null || val === undefined) return;
                        ctx.save();
                        ctx.fillStyle = '#1e40af';
                        ctx.font = 'bold 11px Arial';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';
                        ctx.fillText(parseFloat(val).toFixed(2), point.x, point.y - 6);
                        ctx.restore();
                    });
                });
            }
        };

        // ─── Konfigurasi Y axis 1-4 dengan padding 0.5 ───
        var yAxisConfig = {
            min: 0.5, max: 4.5,
            afterBuildTicks: function(axis) {
                axis.ticks = [1, 2, 3, 4].map(function(v) { return { value: v }; });
            },
            ticks: {
                callback: function(v) { return v; }
            },
            grid: {
                drawOnChartArea: true,
                color: 'rgba(0,0,0,0.1)'
            },
            title: { display: true, text: 'Nilai' }
        };

        // ═══════════════════════════════════════════════════════
        // CHART INDIVIDU SISWA
        // ═══════════════════════════════════════════════════════
        window.addEventListener('init-siswa-chart', function (e) {
            var labels    = e.detail.labels;
            var values    = e.detail.values;
            var nama      = e.detail.nama;
            var kelas     = e.detail.kelas;
            var slug      = e.detail.slug;

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
                        devicePixelRatio: 1.5,
                        animation: {
                            duration: 600,
                            onComplete: function() {
                                var imgEl = document.getElementById('laporan-pdf-chart-img');
                                if (imgEl && canvas) imgEl.src = canvas.toDataURL('image/png');
                            }
                        },
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: true, position: 'top' },
                            tooltip: { callbacks: { label: function(c) { return 'Nilai: ' + c.parsed.y; } } }
                        },
                        scales: {
                            x: { title: { display: true, text: 'Pertemuan' } },
                            y: yAxisConfig
                        }
                    },
                    plugins: [pointLabelsPlugin]
                });

                // ── Tombol Download PDF Individu ──
                var btn = document.getElementById('btnDownloadChart');
                if (btn) {
                    btn.replaceWith(btn.cloneNode(true));
                    btn = document.getElementById('btnDownloadChart');

                    btn.addEventListener('click', function () {
                        if (!window.jspdf || !window.jspdf.jsPDF) { alert('Library PDF tidak termuat.'); return; }

                        const { jsPDF } = window.jspdf;
                        const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });

                        // Judul
                        doc.setFont('helvetica', 'bold'); doc.setFontSize(16); doc.setTextColor(0,0,0);
                        doc.text('Laporan Analisis Individu', 105, 22, { align: 'center' });
                        doc.setDrawColor(0,0,0); doc.setLineWidth(0.5); doc.line(20, 27, 190, 27);

                        // Info Siswa
                        var name     = (document.getElementById('pdf-siswa-nama')?.textContent || '-').trim();
                        var kelasVal = (document.getElementById('pdf-siswa-kelas')?.textContent || '-').trim();
                        var pengajar = (document.getElementById('pdf-siswa-pengajar')?.textContent || '-').trim();
                        var sekolah  = (document.getElementById('pdf-siswa-sekolah')?.textContent || '-').trim();

                        doc.autoTable({
                            startY: 32, margin: { left: 20, right: 20 }, theme: 'plain',
                            styles: { fontSize: 10, cellPadding: 1.5, font: 'helvetica', textColor: [0,0,0] },
                            columnStyles: { 0: { fontStyle: 'bold', cellWidth: 38 }, 1: { cellWidth: 5 } },
                            body: [['Nama',':',name],['Kelas',':',kelasVal],['Pengajar',':',pengajar],['Sekolah',':',sekolah]]
                        });

                        var currentY = doc.lastAutoTable.finalY + 6;

                        // Grafik
                        if (canvas) {
                            try {
                                // Aktifkan label titik hanya saat export
                                window._siswaChartInstance.config._forExport = true;
                                window._siswaChartInstance.update('none');

                                // Gambar background putih + chart ke canvas sementara
                                var offscreen = document.createElement('canvas');
                                offscreen.width  = canvas.width;
                                offscreen.height = canvas.height;
                                var octx = offscreen.getContext('2d');
                                octx.fillStyle = '#ffffff';
                                octx.fillRect(0, 0, offscreen.width, offscreen.height);
                                octx.drawImage(canvas, 0, 0);

                                var imgData = offscreen.toDataURL('image/jpeg', 0.85);
                                var pw = 155, ph = 70;
                                if (canvas.width && canvas.height) {
                                    ph = pw / (canvas.width / canvas.height);
                                    if (ph > 80) ph = 80; if (ph < 45) ph = 45;
                                }
                                doc.addImage(imgData, 'JPEG', 27, currentY, pw, ph);
                                doc.setFont('helvetica','italic'); doc.setFontSize(8); doc.setTextColor(80,80,80);
                                doc.text('Grafik Penilaian Per Pertemuan', 105, currentY + ph + 4, { align: 'center' });
                                currentY += ph + 11;

                                // Kembalikan ke mode normal
                                window._siswaChartInstance.config._forExport = false;
                                window._siswaChartInstance.update('none');
                            } catch(err) { currentY += 5; }
                        }

                        // Rata-rata, Status, Catatan, Rekomendasi — satu tabel agar titik dua sejajar
                        var rata        = (document.getElementById('pdf-siswa-rata')?.textContent || '0.00').trim();
                        var statusV     = (document.getElementById('pdf-siswa-status')?.textContent || '-').trim();
                        var catatan     = (document.getElementById('pdf-siswa-catatan')?.textContent || '-').trim();
                        var rekomendasi = (document.getElementById('pdf-siswa-rekomendasi')?.textContent || '-').trim();

                        doc.autoTable({
                            startY: currentY, margin: { left: 20, right: 20 }, theme: 'plain',
                            styles: { fontSize: 10, cellPadding: 1.5, font: 'helvetica', textColor: [0,0,0], overflow: 'linebreak' },
                            columnStyles: { 0: { fontStyle: 'bold', cellWidth: 38 }, 1: { cellWidth: 5 } },
                            body: [
                                ['Rata-rata Point', ':', rata],
                                ['Status', ':', statusV],
                                ['Catatan', ':', catatan],
                                ['Rekomendasi', ':', rekomendasi]
                            ]
                        });

                        doc.save('laporan individu ' + name.toLowerCase() + (kelasVal ? ' kelas ' + kelasVal.toLowerCase() : '') + '.pdf');
                    });
                }
            }
            doInit();
        });

        // ═══════════════════════════════════════════════════════
        // CHART KELAS
        // ═══════════════════════════════════════════════════════
        window.addEventListener('init-kelas-chart', function (e) {
            var labels    = e.detail.labels;
            var values    = e.detail.values;
            var kelas     = e.detail.kelas;
            var slug      = e.detail.slug;

            function doInitKelas() {
                var canvas = document.getElementById('kelasChart');
                if (!canvas) { setTimeout(doInitKelas, 50); return; }

                if (window._kelasChartInstance) {
                    window._kelasChartInstance.destroy();
                    window._kelasChartInstance = null;
                }

                window._kelasChartInstance = new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Rata-rata Kelas ' + kelas,
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
                        devicePixelRatio: 1.5,
                        animation: {
                            duration: 600,
                            onComplete: function() {
                                var imgEl = document.getElementById('laporan-kelas-pdf-chart-img');
                                if (imgEl && canvas) imgEl.src = canvas.toDataURL('image/png');
                            }
                        },
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: true, position: 'top' },
                            tooltip: { callbacks: { label: function(c) { return 'Rata-rata: ' + (c.parsed.y !== null ? c.parsed.y.toFixed(2) : '-'); } } }
                        },
                        scales: {
                            x: { title: { display: true, text: 'Pertemuan' } },
                            y: yAxisConfig
                        }
                    },
                    plugins: [pointLabelsPlugin]
                });

                // ── Tombol Download PDF Kelas ──
                var btn = document.getElementById('btnDownloadKelasChart');
                if (btn) {
                    btn.replaceWith(btn.cloneNode(true));
                    btn = document.getElementById('btnDownloadKelasChart');

                    btn.addEventListener('click', function () {
                        if (!window.jspdf || !window.jspdf.jsPDF) { alert('Library PDF tidak termuat.'); return; }

                        const { jsPDF } = window.jspdf;
                        const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });

                        // Judul
                        doc.setFont('helvetica','bold'); doc.setFontSize(16); doc.setTextColor(0,0,0);
                        doc.text('Laporan Analisis Kelas', 105, 22, { align: 'center' });
                        doc.setFont('helvetica','normal'); doc.setFontSize(13);
                        doc.text('Kelas ' + kelas, 105, 28, { align: 'center' });
                        doc.setDrawColor(0,0,0); doc.setLineWidth(0.5); doc.line(20, 32, 190, 32);

                        // Info Kelas
                        var sekolah  = (document.getElementById('pdf-kelas-sekolah')?.textContent || '-').trim();
                        var pengajar = (document.getElementById('pdf-kelas-pengajar')?.textContent || '-').trim();

                        doc.autoTable({
                            startY: 37, margin: { left: 20, right: 20 }, theme: 'plain',
                            styles: { fontSize: 10, cellPadding: 1.5, font: 'helvetica', textColor: [0,0,0] },
                            columnStyles: { 0: { fontStyle: 'bold', cellWidth: 38 }, 1: { cellWidth: 5 } },
                            body: [['Sekolah',':',sekolah],['Pengajar',':',pengajar]]
                        });

                        var currentY = doc.lastAutoTable.finalY + 6;

                        // Grafik
                        if (canvas) {
                            try {
                                // Aktifkan label titik hanya saat export
                                window._kelasChartInstance.config._forExport = true;
                                window._kelasChartInstance.update('none');

                                var offscreen = document.createElement('canvas');
                                offscreen.width  = canvas.width;
                                offscreen.height = canvas.height;
                                var octx = offscreen.getContext('2d');
                                octx.fillStyle = '#ffffff';
                                octx.fillRect(0, 0, offscreen.width, offscreen.height);
                                octx.drawImage(canvas, 0, 0);

                                var imgData = offscreen.toDataURL('image/jpeg', 0.85);
                                var pw = 155, ph = 70;
                                if (canvas.width && canvas.height) {
                                    ph = pw / (canvas.width / canvas.height);
                                    if (ph > 80) ph = 80; if (ph < 45) ph = 45;
                                }
                                doc.addImage(imgData, 'JPEG', 27, currentY, pw, ph);
                                doc.setFont('helvetica','italic'); doc.setFontSize(8); doc.setTextColor(80,80,80);
                                doc.text('Grafik Perkembangan Kelas', 105, currentY + ph + 4, { align: 'center' });
                                currentY += ph + 11;

                                window._kelasChartInstance.config._forExport = false;
                                window._kelasChartInstance.update('none');
                            } catch(err) { currentY += 5; }
                        }

                        // Tabel Ranking
                        doc.setFont('helvetica','bold'); doc.setFontSize(10); doc.setTextColor(0,0,0);
                        doc.text('Tabel Ranking Siswa', 20, currentY);
                        doc.autoTable({
                            html: '#pdf-kelas-ranking-table', startY: currentY + 3, margin: { left: 20, right: 20 }, theme: 'grid',
                            headStyles: { fillColor: [243,244,246], textColor: [0,0,0], lineColor: [100,100,100], lineWidth: 0.15, fontStyle: 'bold', halign: 'center' },
                            bodyStyles: { textColor: [0,0,0], lineColor: [100,100,100], lineWidth: 0.15 },
                            columnStyles: { 0: { halign:'center', cellWidth:12 }, 1: { halign:'left' }, 2: { halign:'center', cellWidth:32 }, 3: { halign:'center', cellWidth:38 } },
                            styles: { fontSize: 8, font: 'helvetica', cellPadding: 2.5 }
                        });

                        // Rata-rata kelas
                        var classAvg = (document.getElementById('pdf-kelas-avg')?.textContent || '0.00').trim();
                        doc.setFont('helvetica','bold'); doc.setFontSize(10); doc.setTextColor(0,0,0);
                        doc.text('Rata-rata Point Kelas : ' + classAvg, 20, doc.lastAutoTable.finalY + 7);

                        // Tabel Catatan & Rekomendasi (jika ada)
                        var catatanTable = document.getElementById('pdf-kelas-catatan-table');
                        if (catatanTable) {
                            var catatanY = doc.lastAutoTable.finalY + 15;
                            doc.setFont('helvetica','bold'); doc.setFontSize(10); doc.setTextColor(0,0,0);
                            doc.text('Catatan & Rekomendasi Siswa', 20, catatanY);
                            doc.autoTable({
                                html: '#pdf-kelas-catatan-table', startY: catatanY + 3, margin: { left: 20, right: 20 }, theme: 'grid',
                                headStyles: { fillColor: [243,244,246], textColor: [0,0,0], lineColor: [100,100,100], lineWidth: 0.15, fontStyle: 'bold' },
                                bodyStyles: { textColor: [0,0,0], lineColor: [100,100,100], lineWidth: 0.15, valign: 'top' },
                                columnStyles: {
                                    0: { halign: 'center', cellWidth: 12 },
                                    1: { cellWidth: 38 },
                                    2: { cellWidth: 'auto' },
                                    3: { cellWidth: 'auto' }
                                },
                                styles: { fontSize: 8, font: 'helvetica', cellPadding: 2.5, overflow: 'linebreak' }
                            });
                        }

                        doc.save('laporan kelas ' + (kelas || '').toLowerCase() + '.pdf');
                    });
                }
            }
            doInitKelas();
        });
    </script>
@stop
