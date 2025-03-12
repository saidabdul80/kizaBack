<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomerResource;
use App\Jobs\QueueMail;
use App\Mail\SendMailNoQueue;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\Util;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
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
        return response()->json(["customer"=>new CustomerResource($request->user())]);
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

        DB::beginTransaction();
            try {
            $customer = Customer::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $code = generate_random_number();
            $mailCode = generate_random_number();
            $expired_at = expires_at();
            $customer->update([
                'email_otp' => $mailCode,
                'phone_number_otp' => $code,
                'phone_number_otp_expires_at' => $expired_at,
                'email_otp_expires_at' => $expired_at
            ]);
        
            Util::sendSMS($customer->phone_number, 'Your OTP code is ' . $code . ' and expires in 10 minutes.', 'single');
            Mail::to($customer->email)->send(new SendMailNoQueue('otp','Kiza Email Verification',[
                'name' => $customer->first_name,
                'otp' => $mailCode,
                'expired_at' => $expired_at
            ]));

            DB::commit();
            return response()->json([
                'token' => $customer->createToken('customer_token')->plainTextToken,
                'customer' =>  new CustomerResource($customer)
            ]);
        }catch(\Exception $e){
            DB::rollBack();
            return $e;
            return response()->json(['message' => 'Something went wrong. Please try again.'], 400);
        }
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
            'customer' => new CustomerResource($customer)
        ]);
    }
}
