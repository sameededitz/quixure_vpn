<?php

namespace App\Livewire;

use App\Models\Plan;
use App\Models\Purchase;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Attributes\Validate;
use Livewire\Component;

class AllUsers extends Component
{
    public $users;
    public $selectedUser;
    public $plans;
    #[Validate]
    public $plan_id;

    protected  function rules()
    {
        return [
            'plan_id' => 'required|exists:plans,id',
        ];
    }

    public function mount()
    {
        // Load all users with the 'customer' role
        $this->users = User::where('role', '!=', 'admin')
            ->with(['purchases' => function ($query) {
                $query->latest()->first();
            }])
            ->get();

        $this->plans = Plan::all();
    }

    public function addPurchase()
    {
        $this->validate();

        $plan = Plan::find($this->plan_id);

        $purchase = $this->selectedUser->purchases()
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->first();

        $duration = $plan->duration;

        if ($purchase) {
            $currentExpiresAt = Carbon::parse($purchase->expires_at);

            // Extend the expiration date instead of replacing it
            $newExpiresAt = match ($plan->duration_unit) {
                'day'   => $currentExpiresAt->addDays($duration),
                'week'  => $currentExpiresAt->addWeeks($duration),
                'month' => $currentExpiresAt->addMonths($duration),
                'year'  => $currentExpiresAt->addYears($duration),
                default => $currentExpiresAt->addDays(7),
            };

            // Update the purchase with the new expiration date
            $purchase->update([
                'expires_at' => $newExpiresAt,
            ]);
        } else {
            $expiresAt = match ($plan->duration_unit) {
                'day'   => now()->addDays($duration),
                'week'  => now()->addWeeks($duration),
                'month' => now()->addMonths($duration),
                'year'  => now()->addYears($duration),
                default => now()->addDays(7),
            };
            // Create a new purchase
            $purchase = $this->selectedUser->purchases()->create([
                'plan_id' => $plan->id,
                'started_at' => now(),
                'expires_at' => $expiresAt,
                'is_active' => true,
            ]);
        }

        $this->selectedUser = null;
        $this->plan_id = '';

        // Reload users to update the table
        $this->mount();

        $this->dispatch('close-modal');
        $this->dispatch('alert_add', ['type' => 'success', 'message' => 'Purchase added successfully!']);
    }

    public function openModal(User $user)
    {
        $this->selectedUser = $user;
        $this->plan_id = '';
        $this->dispatch('open-modal');
    }

    public function clearPurchase(User $user)
    {
        $user->purchases()->delete();
        // Reload users to update the table
        $this->mount();

        $this->dispatch('alert_clear', ['type' => 'success', 'message' => 'Purchase cleared successfully!']);
    }

    public function render()
    {
        return view('livewire.all-users');
    }
}
