<?php

namespace App\Http\Controllers;

use App\Http\Requests\OutletRequest;
use App\Models\Outlet;

class OutletController extends Controller
{
    public function index()
    {
        return view('outlets.index', ['outlets' => Outlet::orderBy('name')->paginate(20)]);
    }

    public function create()
    {
        return view('outlets.form', ['outlet' => new Outlet]);
    }

    public function store(OutletRequest $request)
    {
        Outlet::create($request->validated());

        return redirect()->route('outlets.index')->with('status', 'Outlet created.');
    }

    public function edit(Outlet $outlet)
    {
        return view('outlets.form', ['outlet' => $outlet]);
    }

    public function update(OutletRequest $request, Outlet $outlet)
    {
        $outlet->update($request->validated());

        return redirect()->route('outlets.index')->with('status', 'Outlet updated.');
    }

    public function destroy(Outlet $outlet)
    {
        $outlet->update(['active' => false]);
        $outlet->delete();

        return redirect()->route('outlets.index')->with('status', 'Outlet deactivated.');
    }
}
