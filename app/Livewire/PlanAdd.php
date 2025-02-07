<?php

namespace App\Livewire;

use App\Models\Plan;
use Livewire\Attributes\Validate;
use Livewire\Component;

class PlanAdd extends Component
{
    public $plan;

    #[Validate]
    public $name;

    #[Validate]
    public $description;

    #[Validate]
    public $price;

    #[Validate]
    public $duration;

    #[Validate]
    public $duration_unit;

    #[Validate]
    public $device_limit;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:60',
            'description' => 'nullable|string|max:100',
            'price' => 'required',
            'duration' => 'required|numeric',
            'duration_unit' => 'required|in:day,week,month,year',
            'device_limit' => 'nullable|numeric',
        ];
    }

    public function submit()
    {
        $this->validate();
        Plan::create([
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'duration' => $this->duration,
            'duration_unit' => $this->duration_unit,
        ]);

        return redirect()->route('all-plans')->with([
            'status' => 'success',
            'message' => 'Plan Added Successfully',
        ]);
    }
    public function render()
    {
        return view('livewire.plan-add');
    }
}
