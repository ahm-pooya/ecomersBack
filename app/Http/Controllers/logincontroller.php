<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class logincontroller extends Controller
{
    public function loginWithMobile(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'mobile' => ['required', 'digits:11'],
            ],
            [
                'mobile.required' => 'enter your mobile number',
                'mobile.digits' => 'mobile should be 11 digits',
            ]
        );
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()]);
        }
        if (User::where('mobile', request('mobile'))->exists()) {
            $user = User::where('mobile', request('mobile'))->first();
            if ($user) {
                $user->verifycode = rand(1000, 9999);
                $user->verifycode_expiry_time = Carbon::now()->addMinutes(1);
                $user->verifycode_send_time = Carbon::now();
                $user->save();
            }
            return response()->json(['user' => $user]);
        } else {
            return response()->json(['error' => 'user not found']);
        }
    }
    public function verifyCodeForLogin(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'code' => ['required', 'digits:4'],
            ],
            [
                'code.required' => 'enter code',
                'code.digits' => 'code should be 4 digits',
            ]
        );
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()]);
        }
        if (User::where(['id' => $id])->exists()) {
            $user = User::find($id);
            $expirationTime = $user->verifycode_expiry_time;
            if ($expirationTime && Carbon::now()->lt($expirationTime) && $expirationTime != null) {
                if ($request->code == $user->verifycode) {
                    $token = $user->createToken($user->mobile)->accessToken;
                    return response()->json(['token' => $token]);
                } else {
                    return response()->json(['error' => 'Invalid code']);
                }
            } else {
                return response()->json(['error' => 'code expired']);
            }
        } else {
            return response()->json(['error' => 'user not found']);
        }
    }
    public function resendVerifyCode($id)
    {
        if (User::where(['id' => $id])->exists()) {
            $user = User::find($id);
            $verifysend_at = $user->verifycode_send_time;
            $recentlySent = Carbon::parse($verifysend_at)->addMinutes(1)->isFuture();
            if (!$recentlySent) {
                $user->verifycode = rand(1000, 9999);
                $user->verifycode_expiry_time = Carbon::now()->addMinutes(1);
                $user->verifycode_send_time = Carbon::now();
                $user->save();
                return response()->json(['user' => $user]);
            } else {
                return response()->json(['error' => 'wait 2min for resend code']);
            }
        } else {
            return response()->json(['error' => 'user not found']);
        }
    }
    function loginWithPassword()
    {
        if (Auth::attempt([
            'mobile' => request('mobile'),
            'password' => request('password')
        ])) {

            $user = Auth::user();
            $token = $user->createToken(request('mobile'))->accessToken;

            return response()->json(['token' => $token]);
        } else {
            return response()->json(['error' => 'wrong information']);
        }
    }
    public function verifyCodeForResetPassword(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'code' => ['required', 'digits:4'],
            ],
            [
                'code.required' => 'enter code',
                'code.digits' => 'code should be 4 digits',
            ]
        );
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()]);
        }
        if (User::where(['id' => $id])->exists()) {
            $user = User::find($id);
            $expirationTime = $user->verifycode_expiry_time;
            if ($expirationTime && Carbon::now()->lt($expirationTime) && $expirationTime != null) {
                if ($request->code == $user->verifycode) {
                    return response()->json(['user' => $user]);
                } else {
                    return response()->json(['error' => 'unvalid code']);
                }
            } else {
                return response()->json(['error' => 'code expired']);
            }
        } else {
            return response()->json(['error' => 'user not found']);
        }
    }
    public function resetPassword(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'password' => ['required', 'digits:8'], //'confirmed'],
            ],
            [
                'password.required' => 'enter password',
                'password.digits' => 'password should be 8 digits',
                //'password.confirmed' => 'confirm password'
            ]
        );
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()]);
        }
        if (User::where(['id' => $id])->exists()) {
            $user = User::find($id);
            $newpassword = request('password');
            if ($user) {
                $user->password = $newpassword;
            }
            return response()->json(['user' => $user]);
        } else {
            return response()->json(['error' => 'user not found']);
        }
    }
}
