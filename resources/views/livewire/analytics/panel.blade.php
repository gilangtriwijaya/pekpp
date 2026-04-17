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

    <div class="mt-4">
        <h4 class="font-medium">Export Status</h4>
        <div>
            @if($exportStatus)
                <div>ID: {{ $exportStatus['id'] ?? '-' }} — Status: {{ $exportStatus['status'] ?? ($exportStatus['error'] ?? 'n/a') }}</div>
            @else
                <div>No recent export</div>
            @endif
        </div>
    </div>

    <div class="mt-6">
        <h4 class="font-medium">Charts (preview)</h4>
        <canvas id="analyticsChart" width="600" height="250"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:load', function () {
            const ctx = document.getElementById('analyticsChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: { labels: ['A','B','C'], datasets: [{ label: 'Avg Score', data: [3.2,4.1,2.8], backgroundColor: '#4f46e5' }] },
                options: {}
            });
        });
    </script>
</div>
