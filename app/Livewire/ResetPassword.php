<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as RulesPassword;

class ResetPassword extends Component
{
    public $email, $password, $password_confirmation, $token;

    protected function rules()
    {
        return [
            'password' => [
                'required',
                'confirmed',
                RulesPassword::min(8)->mixedCase()->numbers()->symbols()->uncompromised(),
            ],
        ];
    }

    public function mount($token)
    {
        $this->token = $token;
        $this->email = request('email');
    }

    public function resetPassword()
    {
        $this->validate();

        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            session()->flash('status', 'Your password has been reset!');
        } elseif ($status == Password::INVALID_TOKEN) {
            $this->addError('email', 'The provided token is invalid.');
        } else {
            $this->addError('email', __($status));
        }
    }

    public function render()
    {
         /** @disregard @phpstan-ignore-line */
        return view('livewire.reset-password')
        ->extends('layout.admin-guest')
        ->section('admin-guest');
    }
}