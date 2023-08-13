<?php

namespace App\Repositories;

use App\Models\Attendance;
use App\Models\Location;
use App\Models\WorkOrder;
use App\Repositories\BaseRepository;
use App\Traits\Arr;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Class AttendanceRepository
 * @package App\Repositories
 * @version July 2, 2022, 7:21 am UTC
 */
class AttendanceRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'employee_id',
        'location_id',
        'purchase_order_id',
        'type',
        'attendance_at',
        'latitude',
        'longitude',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    public function createValidation($input)
    {
        $typeValues = implode(',', array_keys($this->model()::CHECK_TYPES));
        return Validator::make($input, [
            'type'          => 'required|in:' . $typeValues,
            'work_order_id' => 'required',
            'latitude'      => 'required|numeric|between:-90,90',
            'longitude'     => 'required|numeric|between:-180,180',
        ]);
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Attendance::class;
    }

    public function checkLatLonInRange($input)
    {
        $earthRadius = 6371000;
        $location    = WorkOrder::find($input['work_order_id'])->location;

        $radius = $location->radius;

        $latFrom = deg2rad($location->latitude);
        $lonFrom = deg2rad($location->longitude);
        $latTo   = deg2rad($input['latitude']);
        $lonTo   = deg2rad($input['longitude']);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        $distance = $angle * $earthRadius;

        return ($distance <= $radius) ? true : false;
    }

    public function create($input)
    {
        $input['employee_id'] = Auth::id();
        $input['status']      = Attendance::ACTIVE;
        if (!isset($input['attendance_at'])) {
            $input['attendance_at'] = Carbon::now()->format('Y-m-d H:i:s');
        }

        return parent::create($input);
    }
}