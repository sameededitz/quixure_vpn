<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends Controller
{
    public function addPurchase(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all(),
            ], 400);
        }

        /** @var \App\Models\User $user **/
        $user = Auth::user();

        $plan = Plan::find($request->plan_id);
        if (!$plan) {
            return response()->json([
                'status' => false,
                'message' => 'Plan not found',
            ], 400);
        }

        $purchase = $user->purchases()
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
            $purchase = $user->purchases()->create([
                'plan_id' => $plan->id,
                'started_at' => now(),
                'expires_at' => $expiresAt,
                'is_active' => true,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Purchase created successfully!',
            'purchase' => $purchase
        ], 201);
    }

    public function Status()
    {
        $user = Auth::user();
        /** @var \App\Models\User $user **/
        $purchases = $user->purchases()
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->first();
        return response()->json([
            'status' => true,
            'purchases' => $purchases
        ], 200);
    }
}
