<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends ApiController
{
    public function register(Request $request)
    {
        //validate data from the request
        $data = $request->validate([
            'first_name' => 'required|string|max:200',
            'last_name' =>  'required|string|max:200',
            'username' =>   'required|string|max:200|unique:users,username',
            'email' =>      'required|email|unique:users,email',
            'password' =>   'required|string|min:7',
            'address' =>    'required|string|max:255',
            'longitude' =>  'required|numeric',
            'latitude' =>   'required|numeric',
        ]);

        //create new User and save to DB
        $user = new User([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'address' => $data['address'],
            'longitude' => $data['longitude'],
            'latitude' => $data['latitude'],
            'password' => bcrypt($data['password']),
        ]);
        $save = $user->save();
        if (!$save) {
            return $this->errorResponse('Error occured. Try again.', 500);
        }

        //create token for auth
        $token = $user->createToken('apiToken')->plainTextToken;

        $request_data = [
            'user' => $user,
            'token' => $token
        ];

        return $this->successResponse($request_data, 200);
    }

    public function login(Request $request)
    {
        //validate data from the request
        $data = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        // if credentials are not matching with database, return error message
        if (!Auth::attempt($data)) {
            return $this->errorResponse('Wrong credentials! Please try again.', 401);
        }
        // find a user with the username from the request and create token
        $user = User::where('username', $request->username)->first();
        $token = $user->createToken('apiToken')->plainTextToken;

        $request_data = [
            'user' => $user,
            'token' => $token
        ];
        return $this->successResponse($request_data, 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return $this->successResponse(['message' => 'Successfully logged out', 200], 200);
    }
}
