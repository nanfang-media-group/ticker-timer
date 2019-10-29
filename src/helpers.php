<?php

use NanfangMediaGroup\TickerTimer\TickerTimer;

if (!function_exists('ticker_timer_begin')) {
    function ticker_timer_begin(string $key): void
    {
        TickerTimer::tick($key);
    }
}

if (!function_exists('ticker_timer_end')) {
    function ticker_timer_end(string $key): void
    {
        TickerTimer::tock($key);
    }
}
