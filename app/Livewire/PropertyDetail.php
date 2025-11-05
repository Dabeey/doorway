<?php

namespace App\Livewire;

use App\Models\Property;
use Livewire\Component;

class PropertyDetail extends Component
{
    public Property $property;

    public function mount(Property $property)
    {
        $this->property = $property;
    }

    public function render()
    {
        return view('livewire.property-detail');
    }
}