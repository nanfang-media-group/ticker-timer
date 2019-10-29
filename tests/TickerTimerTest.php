<?php

namespace NanfangMediaGroup\TickerTimer\Tests;

use NanfangMediaGroup\TickerTimer\TickerTimer;

class TickerTimerTest extends FeatureTestCase
{
    public function test_ticker_timer()
    {
        ticker_timer_begin('test1');
        sleep(1);
        ticker_timer_end('test1');

        $this->assertCount(1, TickerTimer::report());
        TickerTimer::flush();

        ticker_timer_begin('test2');
        ticker_timer_begin('test2.a');
        sleep(1);
        ticker_timer_end('test2.a');
        ticker_timer_end('test2');

        $this->assertCount(2, TickerTimer::report());
        TickerTimer::flush();

        ticker_timer_begin('test3');
        ticker_timer_begin('test3.a');
        sleep(1);
        ticker_timer_begin('test3.b');
        sleep(1);
        ticker_timer_begin('test3.c');
        sleep(1);
        ticker_timer_end('test3.a');
        sleep(1);
        ticker_timer_end('test3.c');
        sleep(1);
        ticker_timer_end('test3.b');
        ticker_timer_end('test3');

        $report = TickerTimer::report();
        $this->assertEquals(5, (int) $report['test3']);
        $this->assertEquals(3, (int) $report['test3.a']);
        $this->assertEquals(4, (int) $report['test3.b']);
        $this->assertEquals(2, (int) $report['test3.c']);
    }
}
