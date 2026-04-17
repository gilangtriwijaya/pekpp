<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;

class AnalyticsPanel extends Component
{
    public $periode_id;
    public $tenant_id;
    public $exportStatus = null;

    public function render()
    {
        return view('livewire.analytics.panel');
    }

    public function createExport($type = 'csv')
    {
        $headers = ['Idempotency-Key' => uniqid('exp_', true)];
        $payload = ['type' => $type, 'scope_context' => ['tenant_id' => $this->tenant_id, 'scope_key' => null], 'filters' => ['periode_id' => $this->periode_id]];

        $resp = Http::withHeaders($headers)->post(route('api.analytics.exports.store', [], false), $payload);
        if ($resp->status() === 202) {
            $body = $resp->json();
            $this->exportStatus = ['id' => $body['export_id'], 'status' => 'queued'];
        } else {
            $this->exportStatus = ['error' => $resp->body()];
        }
    }
}
