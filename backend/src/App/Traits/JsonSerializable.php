<?php

namespace TestingTimes\App\Traits;

trait JsonSerializable
{
    public function jsonSerialize(): array
    {
        $ra = [];
        foreach ($this as $k => $v) {
            $ra[$k] = $v;
        }

        return $ra;
    }
}
