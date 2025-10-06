<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AuthSessionStatus extends Component
{
    public $status;

    /**
     * Create a new component instance.
     */
    public function __construct($status = null)
    {
        $this->status = $status;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render()
    {
        return view('components.auth-session-status');
    }
}
