<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Jobs\QueueMail;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|max:17|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
        ]);
        QueueMail::dispatch($user,'account_created', "Account Created");
        return response()->json($user, 201);
    }

    public function login(Request $request)
    {
        return response()->json(['message' => 'Login is not allowed for public routes']);
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['access_token' => $token, 'token_type' => 'Bearer', 'user' => $user]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request)
    {
        return response()->json(["user"=>new UserResource($request->user())]);
    }

    public function unme(Request $request)
    {
        return response()->json([]);
    }

    public function registerCustomer(Request $request)
    {
        $request->validate([
            'first_name' => 'required|min:3',
            'last_name' => 'required|min:3',
            'phone_number' => 'required|unique:customers',
            'email' => 'required|email|unique:customers',
            'password' => 'required|min:6',
        ]);

        $customer = Customer::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'token' => $customer->createToken('customer_token')->plainTextToken,
            'customer' => $customer
        ]);
    }

    public function loginCustomer(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer || !Hash::check($request->password, $customer->password)) {
            throw ValidationException::withMessages(['email' => 'Invalid credentials']);
        }

        return response()->json([
            'access_token' => $customer->createToken('customer_token')->plainTextToken,
            'customer' => $customer
        ]);
    }
}
