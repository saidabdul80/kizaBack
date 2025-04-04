<?php

namespace App\Http\Controllers;

use App\Events\AccountCreated;
use App\Http\Resources\CustomerResource;
use App\Mail\SendMailNoQueue;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $users = User::paginate($request->get('per_page', 10));
        return response()->json(['users' => $users], 200);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        // Dispatch an event when the account is created
        event(new AccountCreated($user, 'forAdmin'));
        return response()->json(['message' => 'Account created successfully'], 200);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return response()->json(['user' => $user], 200);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:6|confirmed',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json(['message' => 'User updated successfully'], 200);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    public function uploadDocuments(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:20480',
            'id' => 'required',
            'type' => 'required|in:nin_slip,international_passport,utility_bills,drivers_license,permanent_residence_card,proof_of_address,profile_picture,receipt',
        ]);
     

        switch ($request->type) {
            case 'receipts':
                $transaction = Transaction::find($request->id);
                $receipts = $transaction->receipts ??[];
                $receipts[] = Util::upload($request->file('file'), 'receipts/'.$request->id.'/'.Str::random(20));
                $transaction->update([
                    'receipts'=> $receipts
                ]);
                break;
            default:
                $user = $request->user();
                $key =$request->type.'_url';
                if($request->type == 'profile_picture'){
                        $key ='picture_url';
                }
                $user->update([
                    $key => Util::upload($request->file('file'), 'uploads/'.$key),
                ]);
                return response()->json( new CustomerResource($user), 200);
                break;
        }
    }

    public function deleteReceipt(Request $request){
        $request->validate([
            'index' => 'required',
            'id' => 'required',
        ]);
     
        $transaction = Transaction::find($request->id);
        $receipts = $transaction->receipts ??[];
        if(empty($receipts)){
            return response()->json(['message' => 'No receipts found'], 400);
        }

        array_splice($receipts,$request->index,1);
        $transaction->update([
            'receipts'=> $receipts
        ]);

        return response()->json(['message' => 'Receipt deleted successfully'], 200);
    }
}
