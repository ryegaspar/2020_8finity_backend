<?php

namespace App\Models;

use App\Mail\InvitationEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

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

    public function send()
    {
        Mail::to($this->email)->send(new InvitationEmail($this));
    }

    public function user()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
