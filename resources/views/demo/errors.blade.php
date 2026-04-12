@extends('layouts.app')

@section('title', 'Error Gallery - Demo')

@section('content')
<div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 min-h-screen -m-6 p-6 rounded-lg">
    
    {{-- Header Section --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-4xl font-bold text-white mb-2 tracking-tight">
                    <span class="text-red-500">⚠️</span> Error Gallery
                </h1>
                <p class="text-slate-400 text-lg">Demo Collection of Application Errors & Edge Cases</p>
            </div>
            <div class="text-right">
                <div class="text-5xl font-bold text-red-400">{{ $totalErrors }}</div>
                <div class="text-slate-400">Errors Captured</div>
            </div>
        </div>

        {{-- Control Panel --}}
        <div class="flex flex-wrap gap-3 mb-6">
            <button onclick="filterBySeverity('all')" class="filter-btn active px-4 py-2 rounded-lg bg-slate-700 text-white hover:bg-slate-600 transition-colors text-sm font-medium" data-severity="all">
                All Errors
            </button>
            <button onclick="filterBySeverity('critical')" class="filter-btn px-4 py-2 rounded-lg bg-red-900/50 text-red-200 hover:bg-red-900 transition-colors text-sm font-medium" data-severity="critical">
                🔴 Critical
            </button>
            <button onclick="filterBySeverity('error')" class="filter-btn px-4 py-2 rounded-lg bg-orange-900/50 text-orange-200 hover:bg-orange-900 transition-colors text-sm font-medium" data-severity="error">
                🟠 Error
            </button>
            <button onclick="filterBySeverity('warning')" class="filter-btn px-4 py-2 rounded-lg bg-yellow-900/50 text-yellow-200 hover:bg-yellow-900 transition-colors text-sm font-medium" data-severity="warning">
                🟡 Warning
            </button>
            <div class="ml-auto">
                <input type="text" id="searchInput" placeholder="Search errors..." class="px-4 py-2 rounded-lg bg-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
    </div>

    {{-- Errors Grid --}}
    <div class="grid grid-cols-1 gap-6">
        @foreach($errors as $error)
        <div class="error-card group" data-severity="{{ $error['severity'] }}" data-searchtext="{{ strtolower($error['title'] . ' ' . $error['message'] . ' ' . $error['type']) }}">
            {{-- Error Header --}}
            <div class="bg-gradient-to-r from-slate-700/80 to-slate-800/80 border border-slate-600/50 backdrop-blur-sm rounded-t-lg p-6 cursor-pointer transition-all hover:border-slate-500/80" onclick="toggleError(this)">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-4 flex-1">
                        {{-- Severity Badge --}}
                        <div class="text-3xl pt-1">
                            @if($error['severity'] === 'critical')
                                <span class="animate-pulse">🔴</span>
                            @elseif($error['severity'] === 'error')
                                <span>🟠</span>
                            @else
                                <span>🟡</span>
                            @endif
                        </div>

                        {{-- Error Info --}}
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-xl font-bold text-white">{{ $error['title'] }}</h3>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full
                                    @if($error['severity'] === 'critical')
                                        bg-red-500/30 text-red-200
                                    @elseif($error['severity'] === 'error')
                                        bg-orange-500/30 text-orange-200
                                    @else
                                        bg-yellow-500/30 text-yellow-200
                                    @endif
                                ">
                                    {{ strtoupper($error['severity']) }}
                                </span>
                                @if(isset($error['code']))
                                    <span class="px-2 py-1 text-xs font-mono bg-slate-600/50 text-slate-300 rounded">{{ $error['code'] }}</span>
                                @endif
                            </div>
                            <p class="text-slate-400 text-sm">{{ $error['type'] }}</p>
                        </div>
                    </div>

                    {{-- Timestamp & Actions --}}
                    <div class="flex flex-col items-end gap-2">
                        <div class="text-xs text-slate-500">{{ $error['timestamp'] }}</div>
                        <button onclick="event.stopPropagation(); copyError('{{ $error['id'] }}')" class="px-3 py-1 text-xs bg-slate-600 hover:bg-slate-500 text-slate-200 rounded transition-colors">
                            📋 Copy
                        </button>
                    </div>
                </div>
            </div>

            {{-- Error Details (Collapsible) --}}
            <div class="error-content hidden bg-slate-800/50 border border-t-0 border-slate-600/50 rounded-b-lg overflow-hidden">
                <div class="p-6 space-y-6">
                    {{-- Error Message --}}
                    <div>
                        <h4 class="text-sm font-semibold text-slate-300 mb-2 uppercase tracking-wide">Error Message</h4>
                        <div class="bg-slate-900/50 border border-red-500/20 rounded-lg p-4 font-mono text-sm text-red-300 break-words">
                            {{ $error['message'] }}
                        </div>
                    </div>

                    {{-- Description --}}
                    @if(isset($error['description']))
                        <div>
                            <h4 class="text-sm font-semibold text-slate-300 mb-2 uppercase tracking-wide">Description</h4>
                            <p class="text-slate-300 leading-relaxed">{{ $error['description'] }}</p>
                        </div>
                    @endif

                    {{-- Validation Details --}}
                    @if(isset($error['details']))
                        <div>
                            <h4 class="text-sm font-semibold text-slate-300 mb-3 uppercase tracking-wide">Validation Errors</h4>
                            <div class="space-y-2">
                                @foreach($error['details'] as $field => $message)
                                    <div class="flex gap-3 items-start">
                                        <span class="text-red-400 font-bold mt-0.5">✗</span>
                                        <div>
                                            <div class="font-mono text-sm text-yellow-300">{{ $field }}</div>
                                            <div class="text-slate-400 text-sm">{{ $message }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Context Info --}}
                    @if(isset($error['current_user']) || isset($error['required_role']) || isset($error['service']) || isset($error['affectedTable']) || isset($error['requested']))
                        <div>
                            <h4 class="text-sm font-semibold text-slate-300 mb-3 uppercase tracking-wide">Context Information</h4>
                            <div class="grid grid-cols-2 gap-4">
                                @if(isset($error['current_user']))
                                    <div class="bg-slate-900/30 rounded-lg p-3">
                                        <div class="text-xs text-slate-500 uppercase">Current User</div>
                                        <div class="text-sm font-mono text-slate-200">{{ $error['current_user'] }}</div>
                                    </div>
                                @endif
                                @if(isset($error['required_role']))
                                    <div class="bg-slate-900/30 rounded-lg p-3">
                                        <div class="text-xs text-slate-500 uppercase">Required Role</div>
                                        <div class="text-sm font-mono text-slate-200">{{ $error['required_role'] }}</div>
                                    </div>
                                @endif
                                @if(isset($error['service']))
                                    <div class="bg-slate-900/30 rounded-lg p-3">
                                        <div class="text-xs text-slate-500 uppercase">Service</div>
                                        <div class="text-sm font-mono text-slate-200">{{ $error['service'] }}</div>
                                    </div>
                                @endif
                                @if(isset($error['timeout']))
                                    <div class="bg-slate-900/30 rounded-lg p-3">
                                        <div class="text-xs text-slate-500 uppercase">Timeout</div>
                                        <div class="text-sm font-mono text-slate-200">{{ $error['timeout'] }}</div>
                                    </div>
                                @endif
                                @if(isset($error['affectedTable']))
                                    <div class="bg-slate-900/30 rounded-lg p-3">
                                        <div class="text-xs text-slate-500 uppercase">Affected Table</div>
                                        <div class="text-sm font-mono text-slate-200">{{ $error['affectedTable'] }}</div>
                                    </div>
                                @endif
                                @if(isset($error['recordCount']))
                                    <div class="bg-slate-900/30 rounded-lg p-3">
                                        <div class="text-xs text-slate-500 uppercase">Record Count</div>
                                        <div class="text-sm font-mono text-slate-200">{{ $error['recordCount'] }}</div>
                                    </div>
                                @endif
                                @if(isset($error['requested']))
                                    <div class="bg-slate-900/30 rounded-lg p-3">
                                        <div class="text-xs text-slate-500 uppercase">Requested URL</div>
                                        <div class="text-sm font-mono text-slate-200 break-all">{{ $error['requested'] }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Stack Trace --}}
                    @if(isset($error['stack']))
                        <div>
                            <h4 class="text-sm font-semibold text-slate-300 mb-3 uppercase tracking-wide">Stack Trace</h4>
                            <div class="bg-slate-900/50 border border-slate-700 rounded-lg overflow-hidden">
                                <div class="divide-y divide-slate-700">
                                    @foreach($error['stack'] as $index => $trace)
                                        <div class="px-4 py-3 hover:bg-slate-800/50 transition-colors">
                                            <div class="flex items-start gap-3">
                                                <span class="text-slate-600 font-mono text-xs mt-0.5">{{ $index + 1 }}</span>
                                                <code class="font-mono text-xs text-slate-300 leading-relaxed">{{ $trace }}</code>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Action Buttons --}}
                    <div class="flex gap-3 pt-4 border-t border-slate-700">
                        <button onclick="copyFullError('{{ $error['id'] }}')" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors text-sm">
                            📋 Copy Full Error
                        </button>
                        <button onclick="downloadError('{{ $error['id'] }}')" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors text-sm">
                            ⬇️ Export
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Footer --}}
    <div class="mt-12 text-center text-slate-400 text-sm">
        <p>This is a demo error gallery for video recording purposes. All errors are simulated.</p>
        <p class="mt-1">Created: {{ now()->format('F d, Y H:i') }} | Environment: {{ env('APP_ENV') }}</p>
    </div>

</div>

<script>
function toggleError(element) {
    const content = element.nextElementSibling;
    content.classList.toggle('hidden');
    element.classList.toggle('border-blue-500/50');
}

function filterBySeverity(severity) {
    const cards = document.querySelectorAll('.error-card');
    const buttons = document.querySelectorAll('.filter-btn');
    
    buttons.forEach(btn => btn.classList.remove('active', 'bg-blue-600'));
    event.target.classList.add('active', 'bg-blue-600');

    cards.forEach(card => {
        if (severity === 'all' || card.dataset.severity === severity) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

document.getElementById('searchInput').addEventListener('input', (e) => {
    const search = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.error-card');
    
    cards.forEach(card => {
        const text = card.dataset.searchtext;
        if (text.includes(search)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});

function copyError(id) {
    const card = document.querySelector(`[data-severity]`);
    const errorText = card.innerText;
    navigator.clipboard.writeText(errorText).then(() => {
        showToast('Error details copied to clipboard!');
    });
}

function copyFullError(id) {
    const card = document.querySelector(`[data-severity]`);
    navigator.clipboard.writeText(card.innerText).then(() => {
        showToast('Full error copied to clipboard!');
    });
}

function downloadError(id) {
    const card = document.querySelector(`[data-severity]`);
    const text = card.innerText;
    const blob = new Blob([text], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `error-${id}-${new Date().getTime()}.txt`;
    a.click();
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg animate-bounce';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2000);
}
</script>

<style>
.filter-btn.active {
    @apply bg-blue-600 text-white;
}
.error-card {
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
    animation: slideIn 0.3s ease-out;
}
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
@endsection
