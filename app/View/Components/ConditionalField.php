<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ConditionalField extends Component
{
    /**
     * A string that will be passed to the name attribute of input or textarea tag.
     */
    public string $inputName;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public bool $inputFieldEnabled = true,
        public string $placeHolder = '',
        public bool $textAreaEnabled = false,
        public string $inputType = 'text',
        public string $field = '',
    ) {
        $this->inputName = $this->toBracketNotation($field);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.conditional-field');
    }

    /**
     * Convert a dot notation into a bracket notation.
     */
    private function toBracketNotation(string $string): string
    {
        $parts = explode('.', $string);

        $first = array_shift($parts);

        return $first.implode('', array_map(fn (string $part) => "[{$part}]", $parts));
    }
}
