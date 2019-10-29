<?php

namespace NanfangMediaGroup\TickerTimer;

use Illuminate\Support\Facades\Cache;

class TickerTimer
{
    public static $tickTocks = [];

    public static function tick(string $key): void
    {
        static::$tickTocks[] = [
            'type' => 'tick',
            'key' => $key,
            'msec' => microtime(true),
        ];
    }

    public static function tock(string $key): void
    {
        static::$tickTocks[] = [
            'type' => 'tock',
            'key' => $key,
            'msec' => microtime(true),
        ];
    }

    public static function report(): array
    {
        return (new ReportGenerator(static::$tickTocks))->generate();
    }

    public static function flush(): void
    {
        static::$tickTocks = [];
    }

    public static function save(): void
    {
        $report = static::report();

        if (!empty($report)) {
            Cache::put('ticker-timer:report', $report, now()->addWeek());
        }
    }
}
