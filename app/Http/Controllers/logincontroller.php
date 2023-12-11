<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class logincontroller extends Controller
{
    private function validateMobile($mobile)
    { 
        $validator = Validator::make(
            ['mobile' => $mobile],
            [
                'mobile' => ['required', 'digits:11', 'regex:/^(9|09)(10|11|12|13|14|15|16|17|18|19|90|91|92|30|33|01|02|03|04|05|35|36|37|38|39|32|20|21|22)\d{7}$/'],
            ],
            [
                'mobile.required' => 'شماره موبایل خود را وارد کنید',
                'mobile.digits' => 'شماره موبایل باید 11 رقمی باشد',
                'mobile.regex' => 'شماره موبایل صحیح نیست'
            ]
        );
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
    }
    public function RegisterMobile(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'mobile' => ['required', 'digits:11', 'regex:/^(9|09)(10|11|12|13|14|15|16|17|18|19|90|91|92|30|33|01|02|03|04|05|35|36|37|38|39|32|20|21|22)\d{7}$/'],
            ],

            [
                'mobile.required' => 'شماره موبایل خود را وارد کنید',
                'mobile.digits' => 'شماره موبایل باید 11 رقمی باشد',
                'mobile.regex' => 'شماره موبایل صحیح نیست'
            ]
        );
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
        if (User::where('mobile', request('mobile'))->exists()) {
            return response()->json(['error' => 'این شماره موبایل وجود دارد'], 400);
        } else {
            $user = User::create([
                'mobile' => request('mobile'),
            ]);
            return response()->json(['user' => $user], 201);
        }
    }
    public function loginWithMobile(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'mobile' => ['required', 'digits:11', 'regex:/^(9|09)(10|11|12|13|14|15|16|17|18|19|90|91|92|30|33|01|02|03|04|05|35|36|37|38|39|32|20|21|22)\d{7}$/'],
            ],

            [
                'mobile.required' => 'شماره موبایل خود را وارد کنید',
                'mobile.digits' => 'شماره موبایل باید 11 رقمی باشد',
                'mobile.regex' => 'شماره موبایل صحیح نیست'
            ]
        );
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
        $mobile = request('mobile');
        if (User::where('mobile', $mobile)->exists()) {
            $user =  User::where('mobile', $mobile)->first();
            User::where('mobile', $mobile)->update([
                'verifycodeForLogin' => rand(1000, 9999),
                'verifycodeExpiryTime' => Carbon::now()->addMinutes(1),
                'verifycodeSendTime' => Carbon::now(),
            ]);
            return response()->json(['user' => $user], 200);
        } else {
            return response()->json(['error' => 'کاربر پیدا نشد']);
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
                'code.required' => 'کد تایید را وارد کنید',
                'code.digits' => 'کد باید 4 رقمی باشد',
            ]
        );
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
        if (User::where(['id' => $id])->exists()) {
            $user = (User::where('id', $id)->exists());
            $expirationTime = $user->verifycodeExpiryTime;
            if ($expirationTime && Carbon::now()->lt($expirationTime) && $expirationTime != null) {
                if ($request->code == $user->verifycodeForlogin) {
                    $token = $user->createToken($request->code,$user->mobile)->accessToken;
                    return response()->json(['token' => $token], 200);
                } else {
                    return response()->json(['error' => 'کد اشتباه است'],400);
                }
            } else {
                return response()->json(['error' => 'کد منقضی شده']);
            }
        } else {
            return response()->json(['error' => 'کاربر پیدا نشد']);
        }
    }
    public function resendVerifyCode($id)
    {
        if (User::where(['id' => $id])->exists()) {
            $user = User::find($id);
            $verifysend_at = $user->verifycode_send_time;
            $recentlySent = Carbon::parse($verifysend_at)->addMinutes(1)->isFuture();
            if (!$recentlySent) {
                $user->verifycodeForLogin = rand(1000, 9999);
                $user->verifycodeExpiryTime = Carbon::now()->addMinutes(1);
                $user->verifycodeSendTime = Carbon::now();
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
            $token = $user->createToken('AppName', [$user->mobile])->accessToken;

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
                $user->save();
            }
            return response()->json(['user' => $user]);
        } else {
            return response()->json(['error' => 'user not found']);
        }
    }
    public function resetPasswordWithAuth()
    {
        if (User::where('id', Auth::user()->id)->exists()) {
            $user = User::find(Auth::user()->id);
            return response()->json(['error' => 'user not found']);
        } else {
            return response()->json(['error' => 'user not found']);
        }
    }
}
