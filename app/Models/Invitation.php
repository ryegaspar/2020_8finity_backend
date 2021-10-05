<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function findByCode($code)
    {
        return self::where('code', $code)->firstOrFail();
    }

    public function hasBeenUsed()
    {
        return $this->admin_id !== null;
    }
}
