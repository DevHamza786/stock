<?php

namespace App\Http\Controllers;

use App\Models\Operator;
use Illuminate\Http\Request;

class OperatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Operator::with(['creator', 'updater']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status') === 'active');
        }

        $operators = $query->orderBy('name')->paginate(15);

        return view('master-data.operators.index', compact('operators'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('master-data.operators.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'employee_id' => 'nullable|string|max:255|unique:operators,employee_id',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'boolean',
        ]);

        Operator::create([
            'name' => $request->name,
            'employee_id' => $request->employee_id,
            'phone' => $request->phone,
            'email' => $request->email,
            'status' => $request->boolean('status', true),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('master-data.operators.index')
            ->with('success', 'Operator created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Operator $operator)
    {
        $operator->load(['creator', 'updater']);
        return view('master-data.operators.show', compact('operator'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Operator $operator)
    {
        return view('master-data.operators.edit', compact('operator'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Operator $operator)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'employee_id' => 'nullable|string|max:255|unique:operators,employee_id,' . $operator->id,
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'boolean',
        ]);

        $operator->update([
            'name' => $request->name,
            'employee_id' => $request->employee_id,
            'phone' => $request->phone,
            'email' => $request->email,
            'status' => $request->boolean('status', true),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('master-data.operators.index')
            ->with('success', 'Operator updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Operator $operator)
    {
        // Check if operator is being used in stock_issued table
        if ($operator->isBeingUsed()) {
            return redirect()->route('master-data.operators.index')
                ->with('error', 'Cannot delete operator that is being used in stock issued records. Please deactivate it instead.');
        }

        $operator->delete();

        return redirect()->route('master-data.operators.index')
            ->with('success', 'Operator deleted successfully.');
    }
}
