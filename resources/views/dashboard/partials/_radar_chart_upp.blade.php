@if(count($radarData) > 0)
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Grafik Perkembangan Aspek (F02)</h2>
            <p class="text-sm text-slate-500 mt-0.5">Bandingkan nilai rata-rata per aspek dari waktu ke waktu</p>
        </div>
        
        <!-- Toggle Period Checkboxes -->
        @if(count($periodeList) > 1)
        <div class="flex flex-wrap gap-3" id="radarPeriodToggles">
            @foreach($periodeList as $idx => $p)
            <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 bg-slate-50 hover:bg-slate-100 border border-slate-200 px-3 py-1.5 rounded-lg cursor-pointer transition-all">
                <input type="checkbox" value="{{ $p['id'] }}" checked class="radar-period-checkbox form-checkbox h-4 w-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                <span>{{ $p['nama'] }}</span>
            </label>
            @endforeach
        </div>
        @else
        <div class="text-xs text-slate-400 font-medium">Belum ada data periode sebelumnya</div>
        @endif
    </div>

    <!-- Canvas -->
    <div class="relative w-full max-w-lg mx-auto h-[350px]">
        <canvas id="radarChartCanvas"></canvas>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rawData = @json($radarData);
        if (!rawData || rawData.length === 0) return;

        const ctx = document.getElementById('radarChartCanvas').getContext('2d');
        
        // Define colors
        const colors = [
            { border: 'rgba(79, 70, 229, 1)', bg: 'rgba(79, 70, 229, 0.15)' }, // Indigo
            { border: 'rgba(16, 185, 129, 1)', bg: 'rgba(16, 185, 129, 0.15)' }, // Emerald
            { border: 'rgba(245, 158, 11, 1)', bg: 'rgba(245, 158, 11, 0.15)' }, // Amber
            { border: 'rgba(59, 130, 246, 1)', bg: 'rgba(59, 130, 246, 0.15)' }  // Blue
        ];

        // Labels come from the aspects of the first data entry
        const labels = Object.keys(rawData[0].nilai_per_aspek);

        function getDatasets(selectedIds) {
            return rawData
                .filter(d => selectedIds.includes(d.periode_id.toString()))
                .map((d, index) => {
                    const colorSet = colors[index % colors.length];
                    const dataValues = labels.map(label => d.nilai_per_aspek[label] ?? 0);
                    
                    return {
                        label: d.label,
                        data: dataValues,
                        borderColor: colorSet.border,
                        backgroundColor: colorSet.bg,
                        borderWidth: 2,
                        pointBackgroundColor: colorSet.border,
                        pointHoverBorderColor: colorSet.border,
                        pointHoverBackgroundColor: '#fff',
                        tension: 0.1
                    };
                });
        }

        // Initially select all
        let checkedIds = Array.from(document.querySelectorAll('.radar-period-checkbox'))
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        // Fallback for single period with no checkboxes
        if (checkedIds.length === 0 && rawData.length > 0) {
            checkedIds = [rawData[0].periode_id.toString()];
        }

        const radarChart = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: labels,
                datasets: getDatasets(checkedIds)
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { family: 'sans-serif', weight: 'bold', size: 12 },
                            boxWidth: 12,
                            padding: 15
                        }
                    },
                    tooltip: {
                        padding: 10,
                        bodyFont: { family: 'sans-serif' }
                    }
                },
                scales: {
                    r: {
                        min: 0,
                        max: 5,
                        ticks: {
                            stepSize: 1,
                            showLabelBackdrop: false,
                            font: { family: 'monospace' }
                        },
                        angleLines: { color: '#e2e8f0' },
                        grid: { color: '#e2e8f0' },
                        pointLabels: {
                            font: {
                                family: 'sans-serif',
                                size: 11,
                                weight: '600'
                            },
                            color: '#475569'
                        }
                    }
                }
            }
        });

        // Event listener for toggles
        document.querySelectorAll('.radar-period-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const currentChecked = Array.from(document.querySelectorAll('.radar-period-checkbox'))
                    .filter(cb => cb.checked)
                    .map(cb => cb.value);
                
                // Prevent unchecking all
                if (currentChecked.length === 0) {
                    this.checked = true;
                    return;
                }

                radarChart.data.datasets = getDatasets(currentChecked);
                radarChart.update();
            });
        });
    });
</script>
@endpush
@else
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6 text-center">
    <div class="py-6 max-w-md mx-auto">
        <div class="bg-indigo-50 text-indigo-600 p-3 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-4 border border-indigo-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.003 9.003 0 1020.945 13H11V3.055z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
            </svg>
        </div>
        <h3 class="text-sm font-bold text-slate-800">Grafik Perkembangan Belum Tersedia</h3>
        <p class="text-xs text-slate-500 mt-1">Selesaikan pengisian mandiri F01 dan tunggu penilaian validator F02 untuk melihat grafik perkembangan aspek pelayanan Anda.</p>
    </div>
</div>
@endif
