<?php

namespace App\Logger;

use App\Models\Log;

trait Loggable
{
    public static function bootLoggable()
    {
        self::created(function ($model) {
            self::log($model, 'created');
        });

        self::updated(function ($model) {
            self::log($model, 'updated');
        });

        self::deleted(function ($model) {
            self::log($model, 'deleted');
        });
    }

    private static function log($model, $action)
    {
        $logger = new Logger($model, $action);
        $logger->record();
    }

    public function logs()
    {
        return $this->morphMany(Log::class, 'loggable');
    }

}
