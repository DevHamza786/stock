<?php

namespace App\View\Components;

use Illuminate\View\Component;

class InputLabel extends Component
{
    public $for;
    public $value;

    /**
     * Create a new component instance.
     */
    public function __construct($for = null, $value = null)
    {
        $this->for = $for;
        $this->value = $value;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render()
    {
        return view('components.input-label');
    }
}
