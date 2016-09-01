<?php

namespace Markoftw\AccountChecker\Functions;

class Functions
{

    /**
     * Clear empty keys inside array.
     *
     * @param $results
     * @return mixed
     */
    public static function cleanKeys($results)
    {
        foreach ($results as $key => $value) {
            if (empty($value)) {
                unset($results[$key]);
            }
        }

        return $results;
    }

    /**
     * Convert arrays to JSON.
     *
     * @param $results
     * @return string
     */
    public static function toJson($results)
    {
        return json_encode($results, JSON_FORCE_OBJECT);
    }

    /**
     * Return page load timer.
     *
     * @return mixed
     */
    public static function timer()
    {
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $timer = $time;
        return $timer;
    }

}
