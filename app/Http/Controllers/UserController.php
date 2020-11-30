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

                ->select('id', 'name', 'email', 'partner_id as partnerId', 'api_token')

                ->select('id', 'name', 'email', 'api_token')

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
            'relationshipCode' => 'nullable|string|min:6|max:6'
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

        // Create relationship code.
        $relationshipCode = $this->createRelationshipCode($user->id);
        $user->relationshipCode = $relationshipCode;

        // Connect user with his partner.
        if($request->relationshipCode) {
            $partnerId = $this->connectUserWithPartner($user->id, $request->relationshipCode);
            if($partnerId) {
                $user->partnerId = $partnerId;
            }
        }

        return response()->json(compact('user','token'),201);
    }

    private function connectUserWithPartner(int $userId, string $relationshipCode)
    {
        $result = DB::table('relationship_codes')
            ->where('code', $relationshipCode)
            ->select('user_id')
            ->get();

        if(!$result->isEmpty()) {
            $partnerId = $result[0]->user_id;

            // Insert partner_id for currently processing user.
            DB::table('users')
                ->where('id', $userId)
                ->update(['partner_id' => $partnerId]);

            // Insert partner_id for his partner.
            DB::table('users')
                ->where('id', $partnerId)
                ->update(['partner_id' => $userId]);

            return $partnerId;
        }
    }

    private function createRelationshipCode(int $userId) {
        $current_date = date('Y-m-d H:i:s');
        $code = rand(100000, 999999);

        DB::table('relationship_codes')
            ->insert([
                'user_id' => $userId,
                'code' => $code,
                'created_at' => $current_date,
                'updated_at' => $current_date
            ]);
        return $code;
    }

}
