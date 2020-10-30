<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use DB;
use App\User;

class UserController extends Controller
{
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {

            $userData = DB::table('users')
                ->where('email', $request->email)
                ->select('id', 'name', 'email', 'partner_id', 'api_token')
                ->get();

            $user = $userData[0];
            $token = $userData[0]->api_token;

            return response()->json(compact('token', 'user'));

        } else {
            return response()->json(['error' => 'invalid_credentials'], 400);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $token = Str::random(60);

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'api_token' => $token,
        ]);

        return response()->json(compact('user','token'),201);
    }

}
