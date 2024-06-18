<?php

namespace App\Http\Controllers;

use App\Models\ActionAuthorization;
use App\Models\Kiosk;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {

        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $credentials = [];

        if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            $credentials['email'] = $request->email;
        } else {
            $credentials['qr_code'] = $request->email;
        }

        if (!Auth::attempt($credentials + ['password' => $request->password])) {
            return response([
                'message' => 'Invalid login details',
                'errors' => [
                    'email' => ['Invalid credentials'],
                    'password' => ['Invalid credentials.'],
                ]
            ], 422);
        }
        $user = Auth::user();
        if(!$user->kioskCashier && !isset($credentials['email'])){
            return response([
                'message' => 'Not yet registered!',
                'errors' => [
                    'email' => ['Not yet registered!'],
                    'password' => ['Not yet registered!'],
                ]
            ], 422);
        }

        $auth = (new ActionAuthorizationController)->authChecker($request);
        ActionAuthorization::create([
            'auth_key' => $auth,
            'user_id' => $user->id,
            'date_expired' => Carbon::now()->addDays(1)
        ]);

        $token = $request->user()->createToken('token')->plainTextToken;

        if (isset($credentials['email'])) {
            return response(['message' => 'Logged in', 'access' => $user->userRole['role_id'], 'token' => $token, 'auth' => $auth], 200);
        } else {
            return response(['message' => 'Kiosk found', 'id' => $user->kioskCashier->kiosk->destination['id'], 'token' => $token, 'auth' => $auth], 200);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response(['message' => 'Logged out'], 200);
    }

    public function kioskRedirect(Request $request)
    {
        $request->validate([
            'qr_code' => 'required',
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('qr_code', 'password'))) {

            return response([
                'message' => 'Invalid login details',
                'errors' => [
                    'qr_code' => ['Invalid credentials'],
                    'password' => ['Invalid credentials.'],
                ]
            ], 422);
        }

        $user = Auth::user();

        return response(['message' => 'Kiosk found', 'id' => $user->kioskCashier->kiosk->destination['id']], 200);
    }
}
