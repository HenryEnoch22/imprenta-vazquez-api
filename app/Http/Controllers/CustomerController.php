<?php

namespace App\Http\Controllers;

use App\Http\Requests\customers\StoreCustomerRequest;
use App\Http\Requests\customers\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::all();
        return response()->json(['data' => $customers], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
           $user = User::create([
                'username' => $data['username'],
                'email' => $data['email'] ?? null,
                'password' => Hash::make($data['password']),
           ]);

           $customerAddress = CustomerAddress::create([
               'postal_code' => $data['postal_code'],
                'address' => $data['address'],
                'locality_name' => $data['locality_name'],
                'federal_entity' => $data['federal_entity'],
                'neighborhood' => $data['neighborhood'],
                'municipality' => $data['municipality'],
                'between_streets' => $data['between_streets'],
                'interior_number' => $data['interior_number'],
                'exterior_number' => $data['exterior_number'],
           ]);

            Customer::create([
                'user_id' => $user->id,
                'address_id' => $customerAddress->id,
                'business_name' => $data['business_name'],
                'representative_name' => $data['representative_name'] ?? null,
                'rfc' => $data['rfc'],
                'phone_number' => $data['phone_number'],
            ]);
        });


        return response()->json(['message' => 'Cliente creado exitosamente'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($customerId)
    {
        $customer = Customer::with('customerAddress', 'user')->find($customerId);

        if (!$customer) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        return response()->json(['data' => $customer], 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, $customerId)
    {
        $data = $request->validated();

        $customer = Customer::with('customerAddress')->find($customerId);

        if (!$customer) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        DB::transaction(function () use ($customer, $data){
            $customer->update([
                'business_name' => $data['business_name'] ,
                'representative_name' => $data['representative_name'] ?? null,
                'rfc' => $data['rfc'],
                'phone_number' => $data['phone_number'],
            ]);

            $customer->customerAddress->update([
                'postal_code' => $data['postal_code'],
                'address' => $data['address'],
                'locality_name' => $data['locality_name'],
                'federal_entity' => $data['federal_entity'],
                'neighborhood' => $data['neighborhood'],
                'municipality' => $data['municipality'],
                'between_streets' => $data['between_streets'],
                'interior_number' => $data['interior_number'] ?? null,
                'exterior_number' => $data['exterior_number'],
            ]);
        });

        return response()->json(['message' => 'Cliente actualizado exitosamente'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $customerId)
    {
        $customer = Customer::with('user', 'customerAddress')->find($customerId);
        if (!$customer) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        DB::transaction(function () use ($customer) {
            $customer->customerAddress->delete();
            $customer->user->delete();
            $customer->delete();
        });

        return response()->json(['message' => 'Cliente eliminado exitosamente'], 200);
    }
}
