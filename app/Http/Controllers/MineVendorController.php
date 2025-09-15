<?php

namespace App\Http\Controllers;

use App\Models\MineVendor;
use Illuminate\Http\Request;

class MineVendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = MineVendor::query();

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('contact_person', 'like', "%{$searchTerm}%")
                  ->orWhere('phone', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('address', 'like', "%{$searchTerm}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $mineVendors = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('stock-management.mine-vendors.index', compact('mineVendors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('stock-management.mine-vendors.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        MineVendor::create($request->all());

        return redirect()->route('stock-management.mine-vendors.index')
            ->with('success', 'Mine vendor created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MineVendor $mineVendor)
    {
        $mineVendor->load(['stockAdditions.product']);
        return view('stock-management.mine-vendors.show', compact('mineVendor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MineVendor $mineVendor)
    {
        return view('stock-management.mine-vendors.edit', compact('mineVendor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MineVendor $mineVendor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $mineVendor->update($request->all());

        return redirect()->route('stock-management.mine-vendors.index')
            ->with('success', 'Mine vendor updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MineVendor $mineVendor)
    {
        if ($mineVendor->stockAdditions()->count() > 0) {
            return redirect()->route('stock-management.mine-vendors.index')
                ->with('error', 'Cannot delete mine vendor with existing stock additions.');
        }

        $mineVendor->delete();

        return redirect()->route('stock-management.mine-vendors.index')
            ->with('success', 'Mine vendor deleted successfully.');
    }

    /**
     * Toggle vendor status.
     */
    public function toggleStatus(MineVendor $mineVendor)
    {
        $mineVendor->update(['is_active' => !$mineVendor->is_active]);

        $status = $mineVendor->is_active ? 'activated' : 'deactivated';

        return redirect()->route('stock-management.mine-vendors.index')
            ->with('success', "Mine vendor {$status} successfully.");
    }
}
