<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate(
            [
                "email" => 'required',
                "password" => 'required'
            ]
        );
        if (Auth::attempt($credentials)) {
            $status = 200;
            $token = $request->user()->createToken('access_token')->plainTextToken;
            $response = [
                'message' => 'success',
                'access_token' => $token,
                'token_type' => 'Bearer',
            ];
            return response($response, $status);
        } else {
            return response()->json([
                "message" => 'Invalid credentials',
            ], 400);
        }
    }
    public function daftar(Request $request)
    {
        $request->validate(
            [
                "email" => 'required',
                "password" => 'required',
                'name' => 'required'
            ]
        );
        $create = User::create(
            [
                "name" => $request->name,
                "email" => $request->email,
                "password" => bcrypt($request->password)
            ]
        );
        if ($create) {
            return response()->json(
                [
                    "message" => "success"
                ]
            );
        } else {
            return response()->json(
                [
                    "message" => "failed"
                ]
            );
        }
    }
    public function showProduct()
    {
        $product = Product::orderBy("name", "asc")->get();
        return response()->json(
            $product
        );
    }
    public function addToCart(Request $request)
    {
        $request->validate(
            [
                "product_id" => 'required',
            ]
        );
        try {
            $product = Product::find($request->product_id)->first();
        $create = Cart::create(
            [
                "product_id" => $request->product_id,
                "user_id" => $request->user()->id,
                "price" => $product->price
            ]
        );
        if ($create) {
            return response()->json(
                [
                    "message" => "success",
                    "total"=>Cart::where("user_id",$request->user()->id)->sum("price")
                ]
            );
        } else {
            return response()->json(
                [
                    "message" => 'failed'
                ]
            );
        }
        } catch (\Throwable $th) {
            return response()->json($th->getMessage());
        }
    }
    public function removeCart(Request $request)
    {
        $delete = Cart::where("user_id",$request->user()->id)->delete();
        try {
            if ($delete) {
                return response()->json(
                    [
                        "message"=>"success",
                        "total"=>"0"
                    ]
                    );
            } else {
                return response()->json(
                    [
                        "message"=>"failed"
                    ]
                    );
            }
        } catch (\Throwable $th) {
            return response()->json($th->getMessage());
        }
    }
}
