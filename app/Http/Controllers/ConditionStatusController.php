<?php

namespace App\Http\Controllers;

use App\Models\ConditionStatus;
use Illuminate\Http\Request;

class ConditionStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ConditionStatus::query();

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
            if ($request->get('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->get('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'sort_order');
        $sortOrder = $request->get('sort_order', 'asc');

        switch ($sortBy) {
            case 'name':
                $query->orderBy('name', $sortOrder);
                break;
            case 'created_at':
                $query->orderBy('created_at', $sortOrder);
                break;
            case 'usage':
                // This would require a more complex query to count usage
                $query->orderBy('name', $sortOrder);
                break;
            default:
                $query->orderBy('sort_order', $sortOrder)->orderBy('name', 'asc');
                break;
        }

        $conditionStatuses = $query->paginate(15)->withQueryString();

        return view('stock-management.condition-statuses.index', compact('conditionStatuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('stock-management.condition-statuses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:condition_statuses,name',
            'description' => 'nullable|string',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|in:on,1,true,0,false'
        ]);

        $data = $request->only(['name', 'description', 'color', 'sort_order']);
        $data['is_active'] = $request->has('is_active');

        // Set default sort order if not provided
        if (empty($data['sort_order'])) {
            $data['sort_order'] = ConditionStatus::max('sort_order') + 1;
        }

        ConditionStatus::create($data);

        return redirect()->route('stock-management.condition-statuses.index')
            ->with('success', 'Condition status created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ConditionStatus $conditionStatus)
    {
        $conditionStatus->load(['stockAdditions.product', 'dailyProductionItems']);

        return view('stock-management.condition-statuses.show', compact('conditionStatus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ConditionStatus $conditionStatus)
    {
        return view('stock-management.condition-statuses.edit', compact('conditionStatus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ConditionStatus $conditionStatus)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:condition_statuses,name,' . $conditionStatus->id,
            'description' => 'nullable|string',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|in:on,1,true,0,false'
        ]);

        $data = $request->only(['name', 'description', 'color', 'sort_order']);
        $data['is_active'] = $request->has('is_active');

        $conditionStatus->update($data);

        return redirect()->route('stock-management.condition-statuses.index')
            ->with('success', 'Condition status updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ConditionStatus $conditionStatus)
    {
        // Check if condition status is being used
        $stockAdditionsCount = $conditionStatus->stockAdditions()->count();
        $dailyProductionItemsCount = $conditionStatus->dailyProductionItems()->count();

        if ($stockAdditionsCount > 0 || $dailyProductionItemsCount > 0) {
            return redirect()->route('stock-management.condition-statuses.index')
                ->with('error', 'Cannot delete condition status that is being used in ' .
                      ($stockAdditionsCount + $dailyProductionItemsCount) . ' records. Please deactivate it instead.');
        }

        $conditionStatus->delete();

        return redirect()->route('stock-management.condition-statuses.index')
            ->with('success', 'Condition status deleted successfully.');
    }

    /**
     * Toggle the active status of a condition status.
     */
    public function toggleStatus(ConditionStatus $conditionStatus)
    {
        $conditionStatus->update(['is_active' => !$conditionStatus->is_active]);

        $status = $conditionStatus->is_active ? 'activated' : 'deactivated';

        return redirect()->route('stock-management.condition-statuses.index')
            ->with('success', "Condition status {$status} successfully.");
    }

    /**
     * Get condition statuses for API/select options.
     */
    public function getActive()
    {
        $conditionStatuses = ConditionStatus::active()->ordered()->get();

        return response()->json($conditionStatuses);
    }
}
