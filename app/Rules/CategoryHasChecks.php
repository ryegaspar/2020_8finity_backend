<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CategoryHasChecks implements Rule
{
    private $typeHasChanged;
    private $hasChecks;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($typeHasChanged, $checkCount)
    {
        $this->typeHasChanged = $typeHasChanged;
        $this->hasChecks = $checkCount > 0;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($this->typeHasChanged && $this->hasChecks)
            return false;

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'cannot change type when category has checks';
    }
}
