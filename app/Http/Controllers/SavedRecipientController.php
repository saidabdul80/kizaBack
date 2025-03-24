<?php

namespace App\Http\Controllers;

use App\Models\SavedRecipient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SavedRecipientController extends Controller
{
    /**
     * Display a listing of the saved recipients.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $filters = $request->all();
        $filters['customerId'] = $user->id;
        return response()->json(SavedRecipient::filter($filters)->paginate(15), 200);
    }

    /**
     * Store a newly created saved recipient in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'   => 'required|exists:customers,id',
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'nullable|string|max:255',
            'phone_number'  => 'required|string|max:15',
            'email'         => 'nullable|email|max:255',
            'bank_name'     => 'required|string|max:255',
            'account_name'  => 'required|string|max:255',
            'account_number'=> 'required|string|max:50',
        ]);
    
        // Update if exists, otherwise create
        $savedRecipient = SavedRecipient::updateOrCreate(
            [
                'customer_id'   => $validated['customer_id'],
                'first_name'    => $validated['first_name'],
                'last_name'     => $validated['last_name'],
                'phone_number'  => $validated['phone_number'],
                'account_number'=> $validated['account_number'],
            ],
            $validated
        );
    
        return response()->json($savedRecipient, Response::HTTP_OK);
    }
    

    /**
     * Display the specified saved recipient.
     */
    public function show($id)
    {
        $savedRecipient = SavedRecipient::find($id);

        if (!$savedRecipient) {
            return response()->json(['message' => 'Saved recipient not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($savedRecipient, Response::HTTP_OK);
    }

    /**
     * Update the specified saved recipient in storage.
     */
    public function update(Request $request, $id)
    {
        $savedRecipient = SavedRecipient::find($id);

        if (!$savedRecipient) {
            return response()->json(['message' => 'Saved recipient not found'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'customer_id'   => 'sometimes|exists:customers,id',
            'first_name'    => 'sometimes|string|max:255',
            'last_name'     => 'sometimes|string|max:255',
            'phone_number'  => 'sometimes|string|max:15|unique:saved_recipients,phone_number,' . $id,
            'email'         => 'sometimes|email|max:255',
            'bank_name'     => 'sometimes|string|max:255',
            'account_name'  => 'sometimes|string|max:255',
            'account_number'=> 'sometimes|string|max:50|unique:saved_recipients,account_number,' . $id,
        ]);

        $savedRecipient->update($validated);

        return response()->json($savedRecipient, Response::HTTP_OK);
    }

    /**
     * Remove the specified saved recipient from storage.
     */
    public function destroy($id)
    {
        $savedRecipient = SavedRecipient::find($id);

        if (!$savedRecipient) {
            return response()->json(['message' => 'Saved recipient not found'], Response::HTTP_NOT_FOUND);
        }

        $savedRecipient->delete();

        return response()->json(['message' => 'Saved recipient deleted successfully'], Response::HTTP_OK);
    }
}
