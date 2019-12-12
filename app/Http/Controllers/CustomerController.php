<?php

namespace App\Http\Controllers;

use App\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{

    /**
     * Create or edit customer
     *
     * @param Request $request
     * @param int|null $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function postCustomer(Request $request, $id = null) {
        if (empty($id)) {
            $this->validate($request, [
                'gender' => 'required',
                'firstname' => 'required',
                'lastname' => 'required',
                'country' => 'required',
                'email' => 'required|email|unique:customers'
            ]);
            $customer = new Customer();
            $customer->bonus = rand(5, 20);
        } else {
            $customer = Customer::findOrFail($id);
        }

        $customer->gender = $request->post('gender', $customer->gender);
        $customer->firstname = $request->post('firstname', $customer->firstname);
        $customer->lastname = $request->post('lastname', $customer->lastname);
        $customer->country = $request->post('country', $customer->country);
        $customer->email = $request->post('email', $customer->email);
        $customer->saveOrFail();

        return response()->json(['customer' => $customer]);
    }
}
