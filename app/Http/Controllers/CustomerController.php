<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            'email' => 'email|unique:customers,email,' . $customer->id,
            'phone_number' => 'unique:customers,phone_number,' . $customer->id,
        ]);

        $customer->update($request->except('password'));

        return response()->json(['message' => 'Customer updated successfully']);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return response()->json(['message' => 'Customer deleted successfully']);
    }
}
