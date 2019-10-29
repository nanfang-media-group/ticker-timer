<?php

namespace NanfangMediaGroup\TickerTimer;

class ReportGenerator
{
    protected $list;

    public function __construct(array $list)
    {
        $this->list = $list;
    }

    public function generate(): array
    {
        $closedTags = collect($this->closeTags());

        // 合并相同标签的耗时数据
        return $closedTags->pluck('key')->unique()->mapWithKeys(function (string $key) use ($closedTags) {
            return [$key => $closedTags->where('key', $key)->sum('time')];
        })->toArray();
    }

    protected function closeTags(): array
    {
        $closedTags = [];
        $tagStack   = [];

        foreach ($this->list as $tag) {
            // 开始标签，入栈
            if ('tick' == $tag['type']) {
                $tagStack[] = $tag;
                continue;
            }

            // 结束标签，闭合
            if ('tock' == $tag['type']) {
                $closedTags[] = $this->closeTag($tagStack, $tag);
            }
        }

        // 不应有未闭合的标签
        if (count(array_filter($tagStack))) {
            throw new Exception('标签闭合错误');
        }

        return $closedTags;
    }

    protected function closeTag(array &$stack, array $tock): array
    {
        $tag = false;

        // 按后进先出的顺序遍历可闭合的标签
        for ($i = count($stack) - 1; $i >= 0; $i--) {
            $tick = $stack[$i];

            // 标签不对称，下一个
            if ($tick['key'] != $tock['key']) {
                continue;
            }

            // 标签成功闭合，计算标签耗时
            $tag = $this->diff($tick, $tock);

            $stack[$i] = null;
            break;
        }

        if (false === $tag) {
            throw new Exception('多余的闭合标签');
        }

        return $tag;
    }

    protected function diff(array $tick, array $tock): array
    {
        return [
            'key' => $tick['key'],
            'time' => $tock['msec'] - $tick['msec'],
        ];
    }
}
