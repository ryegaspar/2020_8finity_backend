<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ActiveAccountHasBalance implements Rule
{
    private $isActive;
    private $hasBalance;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($isActive, $accountBalance)
    {
        $this->isActive = (bool) $isActive;
        $this->hasBalance = $accountBalance > 0;
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
        if ($this->isActive && !$value && $this->hasBalance)
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
        return 'cannot deactivate account with a balance';
    }
}
