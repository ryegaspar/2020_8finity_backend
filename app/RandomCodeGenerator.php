<?php

namespace App;

class RandomCodeGenerator implements InvitationCodeGenerator
{
    public function generate()
    {
        $pool = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 24)), 0, 24);
    }
}
