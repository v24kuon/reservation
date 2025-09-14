<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TextInput extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public ?string $id = null,
        public ?string $name = null,
        public string $type = 'text',
        public ?string $value = null,
        public bool $required = false,
        public ?string $placeholder = null,
        public ?string $class = null,
        public ?string $autocomplete = null,
        public ?int $minlength = null,
        public ?int $maxlength = null,
        public bool $disabled = false,
    ) {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.text-input');
    }
}
