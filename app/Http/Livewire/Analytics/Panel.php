<?php

namespace App\Http\Livewire\Analytics;

use Livewire\Component;

class Panel extends Component
{
    public array $filters = [];

    public function mount()
    {
        // initialize defaults
    }

    public function render()
    {
        return view('livewire.analytics.panel');
    }
}
<?php

namespace App\Http\Livewire\Analytics;

use Livewire\Component;

class Panel extends Component
{
    public $filters = [];

    public function mount()
    {
        $this->filters = [];
    }

    public function render()
    {
        return view('analytics.index');
    }
}
