<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $data = Customer::all();
        return view('master.customers.index', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'nullable|email',
            'phone' => 'required',
            'address' => 'required',
        ]);

        Customer::create($request->all());
        return redirect()->back()->with('success', 'Pelanggan berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $request->validate([
            'name' => 'required',
            'email' => 'nullable|email',
            'phone' => 'required',
            'address' => 'required',
        ]);

        $customer->update($request->all());
        return redirect()->back()->with('success', 'Pelanggan berhasil diperbarui');
    }

    public function destroy($id)
    {
        Customer::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Pelanggan berhasil dihapus');
    }
}
