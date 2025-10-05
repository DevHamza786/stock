<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Machine::with(['creator', 'updater']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status') === 'active');
        }

        // Filter by can_add_stock
        if ($request->filled('can_add_stock')) {
            $query->where('can_add_stock', $request->get('can_add_stock') === 'yes');
        }

        $machines = $query->orderBy('name')->paginate(15);
        
        return view('master-data.machines.index', compact('machines'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('master-data.machines.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:machines,name',
            'description' => 'nullable|string',
            'status' => 'boolean',
            'can_add_stock' => 'boolean',
        ]);

        Machine::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->boolean('status', true),
            'can_add_stock' => $request->boolean('can_add_stock', true),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('master-data.machines.index')
            ->with('success', 'Machine created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Machine $machine)
    {
        $machine->load(['creator', 'updater']);
        return view('master-data.machines.show', compact('machine'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Machine $machine)
    {
        return view('master-data.machines.edit', compact('machine'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Machine $machine)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:machines,name,' . $machine->id,
            'description' => 'nullable|string',
            'status' => 'boolean',
            'can_add_stock' => 'boolean',
        ]);

        $machine->update([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->boolean('status', true),
            'can_add_stock' => $request->boolean('can_add_stock', true),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('master-data.machines.index')
            ->with('success', 'Machine updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Machine $machine)
    {
        // Check if machine is being used in stock_issued table
        if ($machine->isBeingUsed()) {
            return redirect()->route('master-data.machines.index')
                ->with('error', 'Cannot delete machine that is being used in stock issued records. Please deactivate it instead.');
        }

        $machine->delete();

        return redirect()->route('master-data.machines.index')
            ->with('success', 'Machine deleted successfully.');
    }
}
