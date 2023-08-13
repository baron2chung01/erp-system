<?php

namespace App\Traits;

trait Arr
{
    /**
     * Convert under_score type array's keys to camelCase type array's keys
     * @param array $array array to convert
     * @param array $arrayHolder parent array holder for recursive array
     * @return  array   camelCase array
     */
    public static function camelCaseKeys($array, $arrayHolder = array ())
    {
        $camelCaseArray = !empty($arrayHolder) ? $arrayHolder : array ();
        foreach ($array as $key => $val) {
            $newKey = @explode('_', $key);
            array_walk($newKey, function (&$v) {
                return ucwords($v);
            });
            $newKey    = @implode('', $newKey);
            $newKey[0] = strtolower($newKey[0]);
            if (!is_array($val)) {
                $camelCaseArray[$newKey] = $val;
            } else {
                $camelCaseArray[$newKey] = self::camelCaseKeys($val);
            }
        }
        return $camelCaseArray;
    }

    /**
     * Convert camelCase type array's keys to under_score+lowercase type array's keys
     * @param array $array array to convert
     * @param array $arrayHolder parent array holder for recursive array
     * @return  array   under_score array
     */
    public static function underscoreKeys($array, $arrayHolder = array ())
    {
        $underscoreArray = !empty($arrayHolder) ? $arrayHolder : array ();
        foreach ($array as $key => $val) {
            $newKey = preg_replace('/[A-Z]/', '_$0', $key);
            $newKey = strtolower($newKey);
            $newKey = ltrim($newKey, '_');
            if (!is_array($val)) {
                $underscoreArray[$newKey] = $val;
            } else {
                $underscoreArray[$newKey] = self::underscoreKeys($val);
            }
        }
        return self::arrayRemoveNull($underscoreArray);
    }

    /**
     * Convert camelCase type array's values to under_score+lowercase type array's values
     * @param mixed $mixed array|string to convert
     * @param array $arrayHolder parent array holder for recursive array
     * @return  mixed   under_score array|string
     */
    public static function underscoreValues($mixed, $arrayHolder = array ())
    {
        $underscoreArray = !empty($arrayHolder) ? $arrayHolder : array ();
        if (!is_array($mixed)) {
            $newVal = preg_replace('/[A-Z]/', '_$0', $mixed);
            $newVal = strtolower($newVal);
            $newVal = ltrim($newVal, '_');
            return $newVal;
        } else {
            foreach ($mixed as $key => $val) {
                $underscoreArray[$key] = self::underscoreValues($val);
            }
            return $underscoreArray;
        }
    }

    public static function arrayRemoveNull($item)
    {
        if (!is_array($item)) {
            return $item;
        }

        return collect($item)
            ->reject(function ($item) {
                return is_null($item);
            })
            ->flatMap(function ($item, $key) {
                return is_numeric($key)
                    ? [self::arrayRemoveNull($item)]
                    : [$key => self::arrayRemoveNull($item)];
            })
            ->toArray();
    }
}
