<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CategoryHasTransactions implements Rule
{
    private $typeHasChanged;
    private $hasTransactions;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($typeHasChanged, $transactionCount)
    {
        $this->typeHasChanged = $typeHasChanged;
        $this->hasTransactions = $transactionCount > 0;
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
        if ($this->typeHasChanged && $this->hasTransactions)
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
        return 'cannot change type when category has transactions';
    }
}
