<?php

namespace NanfangMediaGroup\TickerTimer\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ticker-timer:report';

    /**
     * The console command description.
     */
    protected $description = '输出耗时打点报告';

    protected $carry = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $report = Cache::get('ticker-timer:report');

        if (!$report) {
            $this->warn('暂无耗时打点数据');
            return;
        }

        $total  = array_sum($report);
        $report = $this->sortReport($report);

        $this->info('原始数据（单位：秒）');
        dump($report);
        $this->output->newLine();

        foreach ($report as $tag => $time) {
            $progressBar = $this->output->createProgressBar(100);

            $this->info($tag);
            $progressBar->start();
            $progressBar->advance(round($time / $total * 100));
            $this->output->newLine();
        }
    }

    protected function sortReport(array $report): array
    {
        // 根据点语法生成立体结构
        $finalStructure = collect($report)
            ->keys()
            ->map(function ($key) {
                $structure = [];

                Arr::set($structure, $key, null);

                return $structure;
            })
            ->reduce(function (array $carry, array $structure) {
                return array_merge_recursive($carry, $structure);
            }, []);

        // 根据维度进行递归排序，用点语法压平立体结构
        $this->applyStructureSort($report, $finalStructure);

        $sortedReport = [];
        $sortedKeys   = collect($this->carry)->map(function (string $key) {
            return Str::replaceFirst('.', '', $key);
        });

        // 重新生成排序后的报告
        foreach ($sortedKeys as $key) {
            if ($time = Arr::get($report, $key)) {
                $sortedReport[$key] = $time;
            }
        }

        return $sortedReport;
    }

    protected function applyStructureSort(array $report, array $structure, ?string $path = null): void
    {
        foreach ($structure as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            if (is_array($value)) {
                $this->carry[] = "$path.$key";
                $this->applyStructureSort($report, $value, "$path.$key");
                continue;
            }

            $this->carry[] = "$path.$key";
        }
    }
}
