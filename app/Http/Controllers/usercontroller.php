<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class usercontroller extends Controller
{
    public function userProfile(Request $request, $id)
    {
        if (User::where(['id' => $id])->exists()) {
            $user = User::where('id', $id);
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'lastname' => 'required',
                    //'image' => 'mimes:png,jpg',
                ],
                [
                    'name.required' => 'نام خود را وارد کنید',
                    'lastname.required' => 'نام خانوادگی خود را وارد کنید',
                    //'mobile.digits' => 'wrong format',
                ]
            );
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }
            User::where('id', $id)->update([
                'name' => $request->name,
                'lastName' => $request->lastName,
            ]);
            return response()->json(['user' => $user],200);
        } else {
            return response()->json(['error' => 'کاربر پیدا نشد'], 404);
        }
    }
}
