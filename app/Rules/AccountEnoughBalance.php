<?php

namespace App\Rules;

use App\Models\Account;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;

class AccountEnoughBalance implements Rule
{
    private $accountId;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->accountId = $id;
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
        $amount = (int)($value) * 100;

        $accountBalance = Account::find($this->accountId)?->balance;

        return $accountBalance >= $amount;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The account does not have enough balance';
    }
}
