<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Upload;
use Ramsey\Uuid\Uuid;
use App\Enums\UploadType;
use Illuminate\Support\Facades\Validator;

class uploadcontroller extends Controller
{
    public function uploadImage(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'file' => ['required', 'mimes:png,jpg'],
                'type' => 'required',
            ],
            [
                'file.required' => 'عکس خود را اپلود کنید',
                'file.mimes' => 'فرمت عکس اشتباه است',
                'type.required' => 'خطا رخ داده است',
            ]
        );
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
        $requestType = $request->type;
        $file = $request->file('file');
        $name = time() . '.' . $file->getClientOriginalExtension();
        $uuid = Uuid::uuid4()->toString();
        if ($requestType == UploadType::User) {
            $path =  public_path('/files/images/user');
            $file_address = '/files/images/user/' . $name;
            $type =  Uploadtype::User;
        }
        if ($requestType == Uploadtype::Product) {
            $path = public_path('/files/images/product');
            $file_address = '/files/images/product/' . $name;
            $type = Uploadtype::Product;
        }
        if ($path != null && $file_address != null && $type != null) {
            $file->move($path, $name);
            $upload = Upload::create([
                'name' => $name,
                'address' => $file_address,
                'file_id' => $uuid,
                'type' => $type,
            ]);
            return response()->json(['id' => $upload->id, 'address' => $upload->address, 'uuid' => $upload->file_id], 200);
        }
    }
    public function showImage($file_id)
    {
        if (Upload::where(['file_id' => $file_id])->exists()) {
            $data = Upload::where('file_id', $file_id)->first();
            return response()->json(['data' => $data], 200);
        } else {
            return response()->json(['error' => 'عکس پیدا نشد'], 404);
        }
    }
}
