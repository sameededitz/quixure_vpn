<?php

namespace App\Livewire;

use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class UserPurchases extends Component
{
    public $user, $plans, $selectedPlan;

    public function mount($userId)
    {
        $this->user = User::with(['activePlan', 'purchases.plan'])->findOrFail($userId);
        $this->plans = Plan::all();
    }

    public function addPlan()
    {
        $plan = Plan::findOrFail($this->selectedPlan);
        $activePurchase = $this->user->activePlan;

        if ($activePurchase) {
            $currentExpiresAt = Carbon::parse($activePurchase->end_date);
            $newExpiresAt = match ($plan->duration_unit) {
                'day' => $currentExpiresAt->addDays($plan->duration),
                'week' => $currentExpiresAt->addWeeks($plan->duration),
                'month' => $currentExpiresAt->addMonths($plan->duration),
                'year' => $currentExpiresAt->addYears($plan->duration),
                default => $currentExpiresAt->addDays(7),
            };

            $activePurchase->update([
                'plan_id' => $plan->id,
                'amount_paid' => $activePurchase->amount_paid + $plan->price,
                'end_date' => $newExpiresAt
            ]);
        } else {
            $expiresAt = match ($plan->duration_unit) {
                'day' => now()->addDays($plan->duration),
                'week' => now()->addWeeks($plan->duration),
                'month' => now()->addMonths($plan->duration),
                'year' => now()->addYears($plan->duration),
                default => now()->addDays(7),
            };

            $this->user->purchases()->create([
                'plan_id' => $plan->id,
                'amount_paid' => $plan->price,
                'start_date' => now(),
                'end_date' => $expiresAt,
                'status' => 'active',
            ]);
        }

        session()->flash('message', 'Plan added or extended successfully!');
        return redirect()->route('user-purchases', $this->user->id);
    }

    public function cancelPurchase()
    {
        if ($this->user->activePlan) {
            $this->user->activePlan->update(['status' => 'cancelled']);
            session()->flash('message', 'Active purchase canceled.');
        } else {
            session()->flash('message', 'No active purchase to cancel.');
        }

        return redirect()->route('user-purchases', $this->user->id);
    }

    public function render()
    {
        return view('livewire.user-purchases');
    }
}