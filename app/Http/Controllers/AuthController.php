<?php

namespace App\Http\Controllers;

use App\Events\CustomerRegistered;
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

    public function loginAdmin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('AdminToken', ['admin'])->plainTextToken;
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

    public function meAdmin(Request $request)
    {
        return response()->json(["user"=>$request->user()]);
    }

    public function unme(Request $request)
    {
        return response()->json([]);
    }

    public function unmeAdmin(Request $request)
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
        //check if the customer is already registered and not verified
        $exists = Customer::where('email', $request->email)->first();
        
        if($exists){
            if(!$exists->email_verified_at){
                event(new CustomerRegistered($exists));
                return response()->json(['message' => 'Email not verified. please check your email for the OTP code.', "type" => "EmailNotVerified"], 400);
            }
            return response()->json(['message' => 'Email already registered'], 400);
        }

        DB::beginTransaction();
            try {
            $customer = Customer::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            event(new CustomerRegistered($customer));
            DB::commit();
            return response()->json([
                //'token' => $customer->createToken('customer_token')->plainTextToken,
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
            return response()->json(['message' => 'Invalid credentials.', "type" => "InvalidCredentials"], 400);
        }

        if(!$customer->email_verified_at){
            event(new CustomerRegistered($customer));
            return response()->json(['message' => 'Email not verified. please check your email for the OTP code.', "type" => "EmailNotVerified"], 400);
        }

        return response()->json([
            'access_token' => $customer->createToken('customer_token')->plainTextToken,
            'customer' => new CustomerResource($customer)
        ]);
    }
}
