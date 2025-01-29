<?php

namespace App\Http\Controllers;

use App\Events\AccountCreated;
use App\Http\Resources\UserResource;
use App\Mail\SendMailNoQueue;
use App\Models\User;
use App\Services\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        return response()->json( UserResource::collection(User::paginate(100)), 200);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json( new UserResource($user), 200);
    }

    public function updateAccountDetails(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'account_number' => 'string|nullable',
            'bank_name' => 'string|nullable',
            'account_name' => 'string|nullable',
        ]);

        // Find the user
        $user = User::findOrFail($id);

        // Update account details
        $user->update($request->only([
            'account_number',
            'bank_name',
            'account_name',
        ]));

        return response()->json([
            'message' => 'Account details updated successfully',
            'user' => $user
        ]);
    }

    public function updateNotificationPreferences(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'notify_security_alerts' => 'boolean',
            'notify_ajo_alerts' => 'boolean',
            'notify_product_announcements' => 'boolean',
            'notify_support_tickets' => 'boolean',
        ]);

        // Find the user
        $user = User::findOrFail($id);

        // Update notification preferences
        $user->update($request->only([
            'notify_security_alerts',
            'notify_ajo_alerts',
            'notify_product_announcements',
            'notify_support_tickets',
        ]));

        return response()->json([
            'message' => 'Notification preferences updated successfully',
            'user' => $user
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users',
            'phone_number' => 'required|string|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
        ]);
        $userdata = $user->toArray();
        $userdata['password'] = $request->password;
        
        Mail::to($user->email)->send(new SendMailNoQueue('account_created', "Account Created", $userdata));
        //AccountCreated::dispatch($userdata);
        return response()->json( new UserResource($user), 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'phone_number' => 'sometimes|required|string|unique:users,phone_number,' . $user->id,
            'password' => 'sometimes|required|string|min:6',
        ]);

        if ($request->has('password')) {
            $request->merge(['password' => Hash::make($request->password)]);
        }

        $user->update($request->all());

        return response()->json( new UserResource($user), 200);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(null, 204);
    }

    public function uploadDocuments(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:20480',
            'id' => 'required|exists:users,id',
            'type' => 'required|in:nin_slip,international_passport,utility_bills,drivers_license,permanent_residence_card,proof_of_address,profile_picture',
        ]);
        $user = User::findOrFail($request->id);
        $key =$request->type.'_url';

        if($request->type == 'profile_picture'){
                $key ='picture_url';
        }
        
        $user->update([
            $key => Util::upload($request->file('file'), 'uploads/'.$key),
        ]);
        return response()->json( new UserResource($user), 200);
    }
}
?>
