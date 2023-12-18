<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class authcontroller extends Controller
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
        return $validator;
    }
    private function validateCode($code)
    {
        $validator = Validator::make(
            ['code' => $code],
            [
                'code' => ['required', 'digits:4'],
            ],
            [
                'code.required' => 'کد تایید را وارد کنید',
                'code.digits' => 'کد باید 4 رقمی باشد',
            ]
        );
        return $validator;
    }
    private function validatePassword($password)
    {
        $validator = Validator::make(
            ['password' => $password],
            [
                'password' => ['required', 'digits:8'],
            ],
            [
                'password.required' => 'رمز را وارد کنید',
                'password.digits' => ' رمز باید 8 رقمی باشد',
            ]
        );
        return $validator;
    }
    private function checkCodeExpiry($expirationTime)
    {
        $currentTime = Carbon::now();
        if ($expirationTime && $currentTime->lt($expirationTime) && $expirationTime != null) {
            return true;
        }
        return false;
    }
    private function checkCodeValidity($code)
    {
        if (request('code') == $code) {
            return true;
        }
        return false;
    }
    private function getUserById($id)
    {
        if (User::where(['id' => $id])->exists()) {
            return User::where('id', $id)->first();
        }
        return false;
    }
    private function getUserByMobile($mobile)
    {
        if (User::where(['mobile' => $mobile])->exists()) {
            return User::where('mobile', $mobile)->first();
        }
        return false;
    }
    private function resendCode($expirationTime)
    {
        $recentlySent =  Carbon::parse($expirationTime)->addMinutes(1)->isFuture();
        if (!$recentlySent) {
            return true;
        }
        return false;
    }
    private function saveCodeForLOgin($mobile)
    {
        User::where('mobile', $mobile)->update([
            'verifycodeForLogin' => rand(1000, 9999),
            'verifycodeExpiryTime' => Carbon::now()->addMinutes(1),
            'verifycodeSendTime' => Carbon::now(),
        ]);
    }
    private function saveCodeForResetPassword($mobile)
    {
        User::where('mobile', $mobile)->update([
            'verifycodeForResetPassword' => rand(1000, 9999),
            'verifycodeExpiryTime' => Carbon::now()->addMinutes(1),
            'verifycodeSendTime' => Carbon::now(),
        ]);
    }
    private function saveCodeForChangeMobile($mobile)
    {
        User::where('mobile', $mobile)->update([
            'verifycodeForResetMobile' => rand(1000, 9999),
            'verifycodeExpiryTime' => Carbon::now()->addMinutes(1),
            'verifycodeSendTime' => Carbon::now(),
        ]);
    }

    public function registerMobile()
    {
        $validator = $this->validateMobile(request('mobile'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
        if (User::where('mobile', request('mobile'))->exists()) {
            return response()->json(['error' => 'این شماره موبایل وجود دارد'], 400);
        } else {
            $user = User::create([
                'mobile' => request('mobile'),
                //'verifycodeSendTime' => Carbon::now(),
                //'verifycodeSendTime' => Carbon::now(),
            ]);
            return response()->json(['user' => $user], 201);
        }
    }
    public function registerPassword($id)
    {
        $validator = $this->validatePassword(request('password'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
        if ($user = $this->getUserById($id)) {
            User::where('id', $id)->update([
                'password' =>  bcrypt(request('password'))
            ]);
            return response()->json(['user' => $user], 200);
        } else {
            return response()->json(['error' => 'کاربر پیدا نشد'], 404);
        }
    }
    public function loginWithMobile()
    {
        $validator = $this->validateMobile(request('mobile'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
        if ($user = $this->getUserByMobile(request('mobile'))) {
            //if($this->checkCodeExpiry($user->verifycodeExpiryTime)){
            $this->saveCodeForLogin(request('mobile'));
            return response()->json(['user' => $user], 200);
        //}
        } else {
            return response()->json(['error' => 'کاربر پیدا نشد'], 404);
        }
    }
    public function verifyCodeForLogin($id)
    {
        if ($user = $this->getUserById($id)) {
            $validator = $this->validatecode(request('code'));
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }
            if ($this->checkCodeExpiry($user->verifycodeExpiryTime)) {
                if ($this->checkCodeValidity($user->verifycodeForLogin)) {
                    $token = $user->createToken($user->mobile)->accessToken;
                    return response()->json(['token' => $token], 200);
                } else {
                    return response()->json(['error' => 'کد اشتباه است'], 400);
                }
            } else {
                return response()->json(['error' => 'کد منقضی شده'], 400);
            }
        } else {
            return response()->json(['error' => 'کاربر پیدا نشد'], 404);
        }
    }
    public function resendVerifyCodeForLogin($id)
    {
        if ($user = $this->getUserById($id)) {
            if ($this->resendCode($user->verifycodeExpiryTime)) {
                $this->saveCodeForLogin($user->mobile);
                return response()->json(['user' => $user], 200);
            } else {
                return response()->json(['error' => 'چند دقیقه صبر کنید'], 400);
            }
        } else {
            return response()->json(['error' => 'کاربر پیدا نشد'], 404);
        }
    }
    function loginWithPassword()
    {
        if (Auth::attempt([
            'mobile' => request('mobile'),
            'password' => request('password')
        ])) {

            $user = Auth::user();
            $token = $user->createToken('AppName')->accessToken;

            return response()->json(['token' => $token], 200);
        } else {
            return response()->json(['error' => 'شماره موبایل یا رمز ورود اشتباه است'], 400);
        }
    }
    public function getMobileForResetPassword()
    {
        $validator = $this->validateMobile(request('mobile'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
        if ($user = $this->getUserByMobile(request('mobile'))) {
            $this->saveCodeForResetPassword(request('mobile'));
            return response()->json(['user' => $user], 200);
        } else {
            return response()->json(['error' => 'کاربر پیدا نشد'], 404);
        }
    }
    public function verifyCodeForResetPassword($id)
    {
        if ($user = $this->getUserById($id)) {
            $validator = $this->validatecode(request('code'));
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }
            if ($this->checkCodeExpiry($user->verifycodeExpiryTime)) {
                if ($this->checkCodeValidity($user->verifycodeForResetPassword)) {
                    return response()->json(['user' => $user], 200);
                } else {
                    return response()->json(['error' => 'کد اشتباه است'], 400);
                }
            } else {
                return response()->json(['error' => 'کد منقضی شده'], 400);
            }
        } else {
            return response()->json(['error' => 'کاربر پیدا نشد'], 404);
        }
    }
    public function resendVerifyCodeForResetPassword($id)
    {
        if ($user = $this->getUserById($id)) {
            if ($this->resendCode($user->verifycodeExpiryTime)) {
                $this->saveCodeForResetPassword($user->mobile);
                return response()->json(['user' => $user], 200);
            } else {
                return response()->json(['error' => 'چند دقیقه صبر کنید'], 400);
            }
        } else {
            return response()->json(['error' => 'کاربر پیدا نشد'], 404);
        }
    }
    public function resetPassword($id)
    {
        $validator = $this->validatePassword(request('password'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
        if ($user = $this->getUserById($id)) {
            User::where('id', $id)->update([
                'password' => request('password')
            ]);
            return response()->json(['user' => $user], 200);
        } else {
            return response()->json(['error' => 'کاربر پیدا نشد'], 404);
        }
    }
    public function getAuth()
    {
        if (User::where('id', Auth::user()->id)->exists()) {
            $user = User::where('id', Auth::user()->id);
            return response()->json(['user' => $user], 200);
        } else {
            return response()->json(['error' => 'unvalid token'], 404);
        }
    }
    public function getMobileForchangeMobile()
    {
        $validator = $this->validateMobile(request('mobile'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
        if ($user = $this->getUserByMobile(request('mobile'))) {
            $this->savecodeForChangeMobile(request('mobile'));
            return response()->json(['user' => $user], 200);
        } else {
            return response()->json(['error' => 'کاربر پیدا نشد'], 404);
        }
    }
    public function verifyCodeForChangeMobile($id)
    {
        if ($user = $this->getUserById($id)) {
            $validator = $this->validatecode(request('code'));
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }
            if ($this->checkCodeExpiry($user->verifycodeExpiryTime)) {
                if ($this->checkCodeValidity($user->verifycodeForChangeMobile)) {
                    return response()->json(['user' => $user], 200);
                } else {
                    return response()->json(['error' => 'کد اشتباه است'], 400);
                }
            } else {
                return response()->json(['error' => 'کد منقضی شده'], 400);
            }
        } else {
            return response()->json(['error' => 'کاربر پیدا نشد'], 404);
        }
    }
    public function resendVerifyCodeForResetMobile($id)
    {
        if ($user = $this->getUserById($id)) {
            if ($this->resendCode($user->verifycodeExpiryTime)) {
                $this->savecodeForChangeMobile($user->mobile);
                return response()->json(['user' => $user], 200);
            } else {
                return response()->json(['error' => 'چند دقیقه صبر کنید'], 400);
            }
        } else {
            return response()->json(['error' => 'کاربر پیدا نشد'], 404);
        }
    }
    public function resetMobile($id)
    {
        $validator = $this->validatecode(request('code'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
        if ($user = $this->getUserById($id)) {
            User::where('id', $id)->update([
                'mobile' => request('mobile'),
            ]);
            return response()->json(['user' => $user], 200);
        } else {
            return response()->json(['error' => 'کاربر پیدا نشد'], 404);
        }
    }
}
