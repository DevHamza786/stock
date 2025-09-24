<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Operator extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'employee_id',
        'phone',
        'email',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Scope for active operators
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Get the user who created the operator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the operator
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
