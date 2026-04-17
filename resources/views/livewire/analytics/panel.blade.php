<div class="p-4">
    <h3 class="text-lg font-semibold">Analytics Panel</h3>

    <div class="mt-3">
        <label>Periode ID</label>
        <input wire:model.defer="periode_id" type="text" class="border rounded px-2 py-1" />
        <label class="ml-3">Tenant ID</label>
        <input wire:model.defer="tenant_id" type="text" class="border rounded px-2 py-1" />
        <button wire:click="createExport('csv')" class="ml-3 px-3 py-1 bg-blue-600 text-white rounded">Export CSV</button>
        <button wire:click="createExport('pdf')" class="ml-2 px-3 py-1 bg-gray-600 text-white rounded">Export PDF</button>
    </div>

    <div class="mt-4" wire:poll.5s="refreshExportStatus">
        <h4 class="font-medium">Export Status</h4>
        <div>
            @if($exportStatus)
                <div>ID: {{ $exportStatus['id'] ?? '-' }} — Status: {{ $exportStatus['status'] ?? ($exportStatus['error'] ?? 'n/a') }}</div>
                @if(isset($exportStatus['status']) && $exportStatus['status'] === 'ready')
                    <button wire:click="downloadExport({{ $exportStatus['id'] }})" class="mt-2 px-3 py-1 bg-green-600 text-white rounded">Download</button>
                @endif
            @else
                <div>No recent export</div>
            @endif
        </div>
    </div>

    <div class="mt-6">
        <h4 class="font-medium">Top UPP (Avg Score)</h4>
        <div wire:ignore>
            <canvas id="analyticsChart" width="600" height="250"></canvas>
        </div>
    </div>

    <div class="mt-6">
        <h4 class="font-medium">Indicator Table</h4>
        <div class="overflow-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="text-left text-sm font-medium text-gray-700">
                        <th class="px-2 py-2">Periode</th>
                        <th class="px-2 py-2">UPP</th>
                        <th class="px-2 py-2">Aspek</th>
                        <th class="px-2 py-2">Indikator</th>
                        <th class="px-2 py-2">Total Responses</th>
                        <th class="px-2 py-2">Avg Score</th>
                        <th class="px-2 py-2">Median</th>
                        <th class="px-2 py-2">Pct Validated</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($rows as $row)
                        <tr class="text-sm">
                            <td class="px-2 py-2">{{ $row->periode_id }}</td>
                            <td class="px-2 py-2">{{ $row->upp_id }}</td>
                            <td class="px-2 py-2">{{ $row->aspek_id }}</td>
                            <td class="px-2 py-2">{{ $row->indikator_id }}</td>
                            <td class="px-2 py-2">{{ $row->total_responses }}</td>
                            <td class="px-2 py-2">{{ $row->avg_score }}</td>
                            <td class="px-2 py-2">{{ $row->median_score }}</td>
                            <td class="px-2 py-2">{{ $row->pct_validated }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $rows->links() }}
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:load', function () {
            const labels = @json($topLabels);
            const data = @json($topData);
            const ctx = document.getElementById('analyticsChart').getContext('2d');
            window.analyticsChart = new Chart(ctx, {
                type: 'bar',
                data: { labels: labels, datasets: [{ label: 'Avg Score', data: data, backgroundColor: '#4f46e5' }] },
                options: { responsive: true, maintainAspectRatio: false }
            });

            window.addEventListener('open-export-url', e => {
                if (e && e.detail && e.detail.url) {
                    window.open(e.detail.url, '_blank');
                }
            });

            window.addEventListener('export-ready', e => {
                Livewire.emit('refreshExportStatus');
            });

            window.addEventListener('show-toast', e => {
                const msg = (e && e.detail && e.detail.message) ? e.detail.message : 'Done';
                alert(msg);
            });

            Livewire.hook('message.processed', (message, component) => {
                // update chart data after Livewire updates
                try {
                    const l = @json($topLabels);
                    const d = @json($topData);
                    if (window.analyticsChart) {
                        window.analyticsChart.data.labels = l;
                        window.analyticsChart.data.datasets[0].data = d;
                        window.analyticsChart.update();
                    }
                } catch (err) {
                    // ignore
                }
            });
        });
    </script>
</div>
