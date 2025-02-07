<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Mail\CustomEmailVerification;
use App\Mail\CustomPasswordReset;
use App\Notifications\CustomResetPassword;
use App\Notifications\CustomVerifyEmailNotification;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'email_verified_at',
        'google_id',
        'apple_id',
        'avatar',
        'registration_date',
        'device_limit',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmailNotification);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPassword($token));
    }

    public function purchases()
    {
        return $this->hasOne(Purchase::class);
    }

    public function devices()
    {
        return $this->hasMany(UserDevice::class);
    }

    public function isPremium()
    {
        return $this->purchases()->where('is_active', true)->where('expires_at', '>', Carbon::now())->exists();
    }

    public function assignFreeTrial()
    {
        if ($this->isPremium()) {
            return;
        }
        if ($this->role === 'normal') {
            Purchase::create([
                'user_id' => $this->id,
                'plan_id' => 1,
                'started_at' => Carbon::now(),
                'expires_at' => Carbon::now()->addDays(3),
                'is_active' => true,
            ]);
        }
    }

    public function getSubscriptionDeviceLimit()
    {
        // If the user has a manually set device limit, use that first
        if (!is_null($this->device_limit)) {
            return $this->device_limit;
        }

        $now = now();

        // Check if the user has an active subscription
        $purchase = $this->purchases()
            ->where('expires_at', '>', $now)
            ->where('is_active', true)
            ->with('plan') // Load the plan relationship
            ->first();

        // If the user has a valid subscription, return the device limit from their plan
        if ($purchase && $purchase->plan) {
            return $purchase->plan->device_limit;
        }

        // Default device limit for non-premium users
        return 1;
    }

    public function canRegisterDevice($deviceId)
    {
        // Check if the device is already registered
        if ($this->devices()->where('device_id', $deviceId)->exists()) {
            return true; // Device already registered
        }

        // Get the user's allowed device limit
        $deviceLimit = $this->getSubscriptionDeviceLimit();

        // Check if the user has reached their device limit
        if ($this->devices()->count() >= $deviceLimit) {
            return false; // Device limit reached
        }

        return true; // Device can be registered
    }

    public function registerDevice($deviceId, $deviceDetails)
    {
        $existingDevice = UserDevice::where('device_id', $deviceId)->first();

        if ($existingDevice) {
            // Check if the device is already assigned to this user
            if ($existingDevice->user_id === $this->id) {
                return false; // Device already registered under the current user
            }

            // Reassign the device to the current user
            $existingDevice->update([
                'user_id' => $this->id,
                'device_token' => $deviceDetails['device_token'],
                'device_name' => $deviceDetails['device_name'],
                'ip_address' => $deviceDetails['ip_address'],
                'platform' => $deviceDetails['platform'],
            ]);

            return $existingDevice;
        }

        // Check if the device can be registered
        if ($this->canRegisterDevice($deviceId)) {
            return $this->devices()->create([
                'device_id' => $deviceId,
                'device_token' => $deviceDetails['device_token'],
                'device_name' => $deviceDetails['device_name'],
                'ip_address' => $deviceDetails['ip_address'],
                'platform' => $deviceDetails['platform'],
            ]);
        }

        return false;
    }

    public function unregisterDevice($deviceId)
    {
        // Delete the device from the user's registered devices
        return $this->devices()->where('device_id', $deviceId)->delete();
    }
}
