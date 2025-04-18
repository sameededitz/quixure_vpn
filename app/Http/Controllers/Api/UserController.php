<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function user()
    {
        /** @var \App\Models\User $user **/
        $user = Auth::user();

        return response()->json([
            'status' => true,
            'user' => $user
        ]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 400);
        }

        /** @var \App\Models\User $user **/
        $user = Auth::user();

        $user->name = $request->name;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }

    public function deleteAccount()
    {
        /** @var \App\Models\User $user **/
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Log the deletion
        Log::info('User account deleted', ['user_id' => $user->id]);

        // Delete user
        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'Account deleted successfully'
        ]);
    }
}
