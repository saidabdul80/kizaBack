<?php
namespace App\Http\Controllers;

use App\Mail\SendMailNoQueue;
use App\Models\Customer;
use App\Services\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class CustomerController extends Controller
{
    public function index()
    {
        return Customer::all();
    }

    public function show($id)
    {
        return Customer::findOrFail($id);
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'phone_number' => 'nullable|string|unique:customers,phone_number,' . $customer->id,
        ]);
    
        $expired_at = expires_at();
        $updateData = $request->except(['password', 'email', 'phone_number']);
    
        // Handle email change & OTP
        if ($request->filled('email') && $customer->email !== $request->email) {
            $mailCode = generate_random_number();
            $updateData['pending_email'] = $request->email;
            $updateData['email_otp'] = $mailCode;
            $updateData['email_otp_expires_at'] = $expired_at;
    
            Mail::to($request->email)->send(new SendMailNoQueue('otp', 'Kiza Email Verification', [
                'name' => $customer->first_name,
                'otp' => $mailCode,
                'expired_at' => $expired_at
            ]));
        }
    
        // Handle phone number change & OTP
        if ($request->filled('phone_number') && $customer->phone_number !== $request->phone_number) {
            $code = generate_random_number();
            $updateData['pending_phone_number'] = $request->phone_number;
            $updateData['phone_number_otp'] = $code;
            $updateData['phone_number_otp_expires_at'] = $expired_at;
    
            Util::sendSMS($request->phone_number, 'Your OTP code is ' . $code . ' and expires in 10 minutes.', 'single');
        }
    
        // Update customer only once
        $customer->update($updateData);
    
        return response()->json(['message' => 'Customer updated successfully']);
    }
    

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return response()->json(['message' => 'Customer deleted successfully']);
    }
}
