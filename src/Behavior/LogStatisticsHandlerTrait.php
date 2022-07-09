<?php

namespace App\Behavior;

trait LogStatisticsHandlerTrait
{
    /**
     * From an array and a key, returns a string
     * @param array $rawResult
     * @param string $key
     * @return array
     */
    public function handleRequestResult(array $rawResult, string $key): array
    {
        $cleanArray = [];
        foreach ($rawResult as $line) {
            $cleanArray[] =  $line[$key];
        }
        return $cleanArray;
    }
}