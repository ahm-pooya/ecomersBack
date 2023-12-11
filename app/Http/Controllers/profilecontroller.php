<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\User;

class profilecontroller extends Controller
{
    public function profile(Request $request,$id){
        if (User::where(['id' => $id])->exists()) {
            $user = User::find($id);
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'lastname' => 'required',
                //'image' => 'mimes:png,jpg',
            ],
            [
                'name.required' => 'enter your name',
                'lastname.required' => 'enter your last name',
                //'mobile.digits' => 'wrong format',
            ]
        );
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()]);
        }
        $user->name = $request->name;
        $user->lastname = $request->lastname;
        $user->save();
    
        return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
    }else{
        return response()->json(['error' => 'not found']);
    }
    }
    public function changeMobile(Request $request)
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
    public function verifyCodeForChangeMobile(Request $request, $id)
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
                    return response()->json(['error' => 'Invalid code']);
                }
            } else {
                return response()->json(['error' => 'code expired']);
            }
        } else {
            return response()->json(['error' => 'user not found']);
        }
    }
    public function resetMobile(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'mobile' => ['required', 'digits:11'], //'confirmed'],
            ],
            [
                'mobile.required' => 'enter mobile',
                'mobile.digits' => 'mobile should be 11 digits',
                //'mobile.confirmed' => 'confirm mobile'
            ]
        );
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()]);
        }
        if (User::where(['id' => $id])->exists()) {
            $user = User::find($id);
            $newmobile = request('mobile');
            if ($user) {
                $user->mobile= $newmobile;
                $user->save();
            }
            return response()->json(['user' => $user]);
        } else {
            return response()->json(['error' => 'user not found']);
        }
    }
}
