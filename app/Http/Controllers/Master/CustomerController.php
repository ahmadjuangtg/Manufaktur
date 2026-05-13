<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();
        if ($request->search) {
            $query->where('name', 'LIKE', "%{$request->search}%")
                  ->orWhere('code', 'LIKE', "%{$request->search}%")
                  ->orWhere('email', 'LIKE', "%{$request->search}%");
        }
        $data = $query->orderBy('updated_at', 'desc')->paginate(20)->withQueryString();
        return view('master.customers.index', compact('data'))->with('search', $request->search);
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'name' => 'required',
            'email' => 'nullable',
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
            'username' => 'required',
            'name' => 'required',
            'email' => 'nullable',
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
