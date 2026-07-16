@if(count($progressPerUPP) > 0)
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Grafik Progres Evaluasi UPP</h2>
            <p class="text-sm text-slate-500 mt-0.5">Persentase penyelesaian pengisian formulir mandiri F01 untuk seluruh UPP</p>
        </div>
        <div class="flex items-center gap-2">
            <button id="btnExportJPG" type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-300 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-50 transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                Export JPG
            </button>
            <button id="btnExportPDF" type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-300 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-50 transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M8 13h2"/><path d="M8 17h2"/><path d="M14 13h2"/><path d="M14 17h2"/></svg>
                Export PDF
            </button>
        </div>
    </div>

    <!-- Canvas Container -->
    <div class="relative w-full overflow-y-auto" style="max-height: 480px;">
        <div style="height: {{ max(200, count($progressPerUPP) * 35) }}px; min-height: 200px;">
            <canvas id="progressChartCanvas"></canvas>
        </div>
    </div>

    <!-- Chart Legend -->
    <div class="flex flex-wrap justify-center gap-6 mt-4 pt-4 border-t border-slate-100 text-xs font-semibold text-slate-600">
        <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 bg-slate-300 rounded"></span>
            <span>Belum Mulai</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 bg-amber-400 rounded"></span>
            <span>Sedang Mengisi</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 bg-blue-500 rounded"></span>
            <span>Menunggu Validasi</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 bg-emerald-500 rounded"></span>
            <span>Selesai</span>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dataList = @json($progressPerUPP);
        if (!dataList || dataList.length === 0) return;

        // Sort dataList by progress percentage desc
        dataList.sort((a, b) => b.persen_progress - a.persen_progress);

        const labels = dataList.map(item => item.nama_upp);
        const dataValues = dataList.map(item => item.persen_progress);
        
        // Map status to colors
        const bgColors = dataList.map(item => {
            if (item.status === 'belum_mulai') return 'rgba(203, 213, 225, 0.85)'; // slate-300
            if (item.status === 'draft' || item.status === 'rolled_back' || item.status === 'sedang_mengisi') return 'rgba(251, 191, 36, 0.85)'; // amber-400
            if (item.status === 'submitted' || item.status === 'menunggu_validasi') return 'rgba(59, 130, 246, 0.85)'; // blue-500
            if (item.status === 'selesai') return 'rgba(16, 185, 129, 0.85)'; // emerald-500
            return 'rgba(203, 213, 225, 0.85)';
        });

        const borderColors = dataList.map(item => {
            if (item.status === 'belum_mulai') return 'rgba(148, 163, 184, 1)';
            if (item.status === 'draft' || item.status === 'rolled_back' || item.status === 'sedang_mengisi') return 'rgba(217, 119, 6, 1)';
            if (item.status === 'submitted' || item.status === 'menunggu_validasi') return 'rgba(29, 78, 216, 1)';
            if (item.status === 'selesai') return 'rgba(4, 120, 87, 1)';
            return 'rgba(148, 163, 184, 1)';
        });

        const ctx = document.getElementById('progressChartCanvas').getContext('2d');
        const chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Progres Pengisian (%)',
                    data: dataValues,
                    backgroundColor: bgColors,
                    borderColor: borderColors,
                    borderWidth: 1.5,
                    borderRadius: 4,
                    barThickness: 16
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Progres: ${context.raw}%`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        min: 0,
                        max: 100,
                        ticks: {
                            stepSize: 20,
                            callback: function(value) { return value + '%'; }
                        },
                        grid: { color: '#f1f5f9' }
                    },
                    y: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 11, weight: '600' },
                            color: '#334155'
                        }
                    }
                }
            }
        });

        // Legend items definition
        const legendItems = [
            { color: 'rgba(203, 213, 225, 0.85)', border: 'rgba(148, 163, 184, 1)', label: 'Belum Mulai' },
            { color: 'rgba(251, 191, 36, 0.85)',  border: 'rgba(217, 119, 6, 1)',   label: 'Sedang Mengisi' },
            { color: 'rgba(59, 130, 246, 0.85)',  border: 'rgba(29, 78, 216, 1)',   label: 'Menunggu Validasi' },
            { color: 'rgba(16, 185, 129, 0.85)',  border: 'rgba(4, 120, 87, 1)',    label: 'Selesai' },
        ];

        // Draw legend on canvas context
        function drawLegendOnCanvas(tCtx, canvasWidth, yStart, dpr) {
            // Ukuran absolut (px) — tidak dikalikan dpr agar proporsional di resolusi tinggi
            const boxSize     = 14;
            const gap         = 6;
            const fontSize    = 11;
            const itemSpacing = 120;

            tCtx.font = `${fontSize}px sans-serif`;
            const totalWidth = legendItems.length * itemSpacing - gap;
            let x = (canvasWidth - totalWidth) / 2;
            const y = yStart;

            legendItems.forEach(item => {
                // Draw colored box
                tCtx.fillStyle = item.color;
                tCtx.strokeStyle = item.border;
                tCtx.lineWidth = 1;
                tCtx.beginPath();
                tCtx.roundRect(x, y - boxSize + 2, boxSize, boxSize, 2);
                tCtx.fill();
                tCtx.stroke();

                // Draw label
                tCtx.fillStyle = '#334155';
                tCtx.textAlign = 'left';
                tCtx.fillText(item.label, x + boxSize + gap * 0.6, y);

                x += itemSpacing;
            });
        }

        function getHighResImage(format, callback) {
            // Get original options
            const originalRatio = chartInstance.options.devicePixelRatio || window.devicePixelRatio;
            const originalAnimation = chartInstance.options.animation;
            
            // Set for high res
            const dpr = 3;
            chartInstance.options.devicePixelRatio = dpr;
            chartInstance.options.animation = false;
            chartInstance.update();
            
            // Allow render to complete
            setTimeout(() => {
                const canvas = document.getElementById('progressChartCanvas');
                const tempCanvas = document.createElement('canvas');
                
                let paddingTop = 0;
                let paddingBottom = 0;
                
                if (format === 'jpg') {
                    paddingTop = 110 * dpr;
                    paddingBottom = 80 * dpr;  // extra space for legend + footer
                }
                
                tempCanvas.width = canvas.width;
                tempCanvas.height = canvas.height + paddingTop + paddingBottom;
                const tCtx = tempCanvas.getContext('2d');
                
                // Ensure white background
                tCtx.fillStyle = '#ffffff';
                tCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
                
                if (format === 'jpg') {
                    const maxWidth = tempCanvas.width - (40 * dpr);
                    // Draw title
                    tCtx.fillStyle = '#1e293b';
                    tCtx.font = `bold ${24 * dpr}px sans-serif`;
                    tCtx.textAlign = 'center';
                    tCtx.fillText('Grafik Progres Evaluasi UPP', tempCanvas.width / 2, 50 * dpr, maxWidth);
                    
                    // Draw subtitle
                    tCtx.fillStyle = '#64748b';
                    tCtx.font = `${14 * dpr}px sans-serif`;
                    tCtx.fillText('Persentase penyelesaian pengisian formulir mandiri F01 untuk seluruh UPP', tempCanvas.width / 2, 80 * dpr, maxWidth);
                }
                
                // Draw chart
                tCtx.drawImage(canvas, 0, paddingTop);
                
                if (format === 'jpg') {
                    // Draw separator line above legend
                    const sepY = canvas.height + paddingTop + 18 * dpr;
                    tCtx.strokeStyle = '#e2e8f0';
                    tCtx.lineWidth = 1.5;
                    tCtx.beginPath();
                    tCtx.moveTo(20 * dpr, sepY);
                    tCtx.lineTo(tempCanvas.width - 20 * dpr, sepY);
                    tCtx.stroke();

                    // Draw legend
                    drawLegendOnCanvas(tCtx, tempCanvas.width, sepY + 32 * dpr, dpr);

                    const maxWidth = tempCanvas.width - (40 * dpr);
                    // Draw footer
                    tCtx.fillStyle = '#94a3b8';
                    tCtx.font = `${12 * dpr}px sans-serif`;
                    tCtx.textAlign = 'center';
                    const dateStr = new Date().toLocaleString('id-ID');
                    tCtx.fillText(`Dokumen ini diunduh secara resmi dari Aplikasi LAYANI Mandiri SISTAGOR pada ${dateStr}`, tempCanvas.width / 2, tempCanvas.height - (18 * dpr), maxWidth);
                }
                
                const imgData = tempCanvas.toDataURL('image/jpeg', 1.0);
                
                // Restore
                chartInstance.options.devicePixelRatio = originalRatio;
                chartInstance.options.animation = originalAnimation;
                chartInstance.update();
                
                callback(imgData, tempCanvas.width, tempCanvas.height);
            }, 250);
        }

        // Export JPG
        document.getElementById('btnExportJPG').addEventListener('click', function() {
            getHighResImage('jpg', (imgData) => {
                const link = document.createElement('a');
                link.download = 'Grafik_Progres_Evaluasi_UPP.jpg';
                link.href = imgData;
                link.click();
            });
        });

        // Export PDF
        document.getElementById('btnExportPDF').addEventListener('click', function() {
            if (typeof window.jspdf === 'undefined') {
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
                script.onload = () => exportToPDF();
                document.head.appendChild(script);
            } else {
                exportToPDF();
            }
        });

        function exportToPDF() {
            getHighResImage('pdf', (imgData, imgWidth, imgHeight) => {
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('p', 'mm', 'a4');
                
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = pdf.internal.pageSize.getHeight();
                
                const margin = 10;
                const footerHeight = 15;
                const maxWidth = pdfWidth - (margin * 2);
                
                // Scale width to fit margin, height proportionally
                const ratio = maxWidth / imgWidth;
                const finalWidth = maxWidth;
                const finalHeight = imgHeight * ratio;
                
                let remainingHeight = finalHeight;
                let currentY = 0;
                let page = 1;
                
                while (remainingHeight > 0) {
                    if (page > 1) {
                        pdf.addPage();
                    }
                    
                    // White background
                    pdf.setFillColor(255, 255, 255);
                    pdf.rect(0, 0, pdfWidth, pdfHeight, 'F');
                    
                    let yPos = margin;
                    
                    if (page === 1) {
                        yPos = margin + 20; // Image starts below subtitle
                    }
                    
                    // Add image, shifted up by currentY
                    pdf.addImage(imgData, 'JPEG', margin, yPos - currentY, finalWidth, finalHeight);
                    
                    // Mask top area (header)
                    pdf.setFillColor(255, 255, 255);
                    pdf.rect(0, 0, pdfWidth, yPos, 'F');
                    
                    // Mask bottom area (footer)
                    pdf.rect(0, pdfHeight - footerHeight, pdfWidth, footerHeight, 'F');
                    
                    if (page === 1) {
                        // Title
                        pdf.setFontSize(16);
                        pdf.setFont("helvetica", "bold");
                        pdf.setTextColor(30, 41, 59); // slate-800
                        pdf.text('Grafik Progres Evaluasi UPP', pdfWidth / 2, margin + 5, { align: 'center' });
                        
                        // Subtitle
                        pdf.setFontSize(10);
                        pdf.setFont("helvetica", "normal");
                        pdf.setTextColor(100, 116, 139); // slate-500
                        pdf.text('Persentase penyelesaian pengisian formulir mandiri F01 untuk seluruh UPP', pdfWidth / 2, margin + 12, { align: 'center' });
                    }

                    // Draw legend on last page (or page 1 if only 1 page)
                    if (remainingHeight - (pdfHeight - yPos - footerHeight) <= 0) {
                        const legendColors = [
                            { r: 203, g: 213, b: 225, label: 'Belum Mulai' },
                            { r: 251, g: 191, b: 36,  label: 'Sedang Mengisi' },
                            { r: 59,  g: 130, b: 246, label: 'Menunggu Validasi' },
                            { r: 16,  g: 185, b: 129, label: 'Selesai' },
                        ];
                        const legendY = pdfHeight - footerHeight - 10;
                        const boxW = 4;
                        const boxH = 3;
                        const itemW = 38;
                        const totalLegendW = legendColors.length * itemW - 2;
                        let lx = (pdfWidth - totalLegendW) / 2;

                        // Separator line
                        pdf.setDrawColor(226, 232, 240);
                        pdf.setLineWidth(0.3);
                        pdf.line(margin, legendY - 6, pdfWidth - margin, legendY - 6);

                        legendColors.forEach(item => {
                            pdf.setFillColor(item.r, item.g, item.b);
                            pdf.setDrawColor(item.r * 0.7, item.g * 0.7, item.b * 0.7);
                            pdf.setLineWidth(0.3);
                            pdf.roundedRect(lx, legendY - boxH, boxW, boxH, 0.5, 0.5, 'FD');

                            pdf.setFontSize(7);
                            pdf.setFont("helvetica", "normal");
                            pdf.setTextColor(51, 65, 85);
                            pdf.text(item.label, lx + boxW + 1.5, legendY - 0.3);

                            lx += itemW;
                        });
                    }
                    
                    // Draw Footer on every page
                    pdf.setFontSize(9);
                    pdf.setFont("helvetica", "italic");
                    pdf.setTextColor(148, 163, 184); // slate-400
                    const dateStr = new Date().toLocaleString('id-ID');
                    const footerText = `Dokumen ini diunduh secara resmi dari Aplikasi LAYANI Mandiri SISTAGOR pada ${dateStr} - Halaman ${page}`;
                    pdf.text(footerText, pdfWidth / 2, pdfHeight - (margin / 2), { align: 'center' });
                    
                    // Calculate available height for image on this page
                    let pageContentHeight = pdfHeight - yPos - footerHeight;
                    currentY += pageContentHeight;
                    remainingHeight -= pageContentHeight;
                    page++;
                }
                
                pdf.save('Grafik_Progres_Evaluasi_UPP.pdf');
            });
        }
    });
</script>
@endpush
@endif
