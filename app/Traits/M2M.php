<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait M2M
{
    // must follow naming convention: {FirstModel}Has{SecondModel}
    public static function getName($pivotNameSpace)
    {
        $pivotModel = app($pivotNameSpace);
        $nameSpace = str_replace('App\\Models\\', '', $pivotNameSpace);
        return preg_split('/Has/', $nameSpace);

        // return format: [$firstModel, $secondModel]
    }

    public static function storeM2M($firstModelID, $inputIDList, $pivotNameSpace)
    {
        $pivotModel = app($pivotNameSpace);

        [$firstModel, $secondModel] = self::getName($pivotNameSpace);

        foreach ($inputIDList as $secondModelID) {
            $pivotModel::create([
                Str::snake($firstModel) . '_id'  => $firstModelID,
                Str::snake($secondModel) . '_id' => $secondModelID,
            ]);
        }
    }

    public static function updateM2M($firstModelID, $oriIDList, $inputIDList, $pivotNameSpace)
    {
        $pivotModel = app($pivotNameSpace);

        [$firstModel, $secondModel] = self::getName($pivotNameSpace);

        $deleteIDList = array_diff($oriIDList, $inputIDList);
        $createIDList = array_diff($inputIDList, $oriIDList);

        foreach ($deleteIDList as $secondModelID) {
            $pivotModel::where(Str::snake($firstModel) . '_id', $firstModelID)->where(Str::snake($secondModel) . '_id', $secondModelID)->forceDelete();
        }
        foreach ($createIDList as $secondModelID) {
            $pivotModel::create(
                [
                    Str::snake($firstModel) . '_id'  => $firstModelID,
                    Str::snake($secondModel) . '_id' => $secondModelID,
                ]
            );
        }

    }
}
