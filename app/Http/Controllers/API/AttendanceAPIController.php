<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateAttendanceAPIRequest;
use App\Http\Requests\API\UpdateAttendanceAPIRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Repositories\AttendanceRepository;
use App\Traits\Arr;
use Illuminate\Http\Request;
use Response;

/**
 * Class AttendanceController
 * @package App\Http\Controllers\API
 */
class AttendanceAPIController extends AppBaseController
{
    /** @var  AttendanceRepository */
    private $attendanceRepository;

    public function __construct(AttendanceRepository $attendanceRepo)
    {
        $this->attendanceRepository = $attendanceRepo;
    }

    /**
     * Display a listing of the Attendance.
     * GET|HEAD /attendances
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->authorize('attendance_view');

        // super admin gets all attendance

        // office/site admin gets leader, worker, subcon

        // return response()->json(auth()->user());

        list($input, $current, $pageSize) = $this->getInput($request);

        if (!auth()->user()->hasRole('Super Admin')) {

            $input['employee.roles.code'] = ['Leader', 'Subcontractor', 'Worker'];
            $query = Attendance::with('employee')->whereRelation('employee.roles', fn($q) =>
                $q->whereIn('code', ['Leader', 'Subcontractor', 'Worker'])->orderBy('role.id')
            )->orderBy('attendances.updated_at', 'desc');

            $total = $query->get()->count();

            $attendances = $query->skip($current)
                ->take($pageSize)
                ->get();
        } else {

            $attendances = $this->attendanceRepository->all(
                $input,
                $current,
                $pageSize,
                ['employee']
            );

            $total = count($this->attendanceRepository->all(
                $input
            ));
        }

        return $this->sendResponse([
            'data'  => AttendanceResource::collection($attendances),
            'total' => $total,
        ], 'Attendances retrieved successfully');
    }

    /**
     * Store a newly created Attendance in storage.
     * POST /attendances
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $this->authorize('attendance_create');

        $input = $request->all();

        $input = Arr::underscoreKeys($input);

        $attendance = $this->attendanceRepository->create($input);

        return $this->sendResponse(AttendanceResource::make($attendance), 'Attendance saved successfully');
    }

    /**
     * Display the specified Attendance.
     * GET|HEAD /attendances/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $this->authorize('attendance_view');

        /** @var Attendance $attendance */
        $attendance = $this->attendanceRepository->find($id, ['employee']);

        if (empty($attendance)) {
            return $this->sendError('Attendance not found');
        }

        return $this->sendResponse(AttendanceResource::make($attendance), 'Attendance retrieved successfully');
    }

    /**
     * Update the specified Attendance in storage.
     * PUT/PATCH /attendances/{id}
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $this->authorize('attendance_update');

        $input = $request->all();

        $input = Arr::underscoreKeys($input);

        /** @var Attendance $attendance */
        $attendance = $this->attendanceRepository->find($id);

        if (empty($attendance)) {
            return $this->sendError('Attendance not found');
        }

        $attendance = $this->attendanceRepository->update($input, $id);

        return $this->sendResponse(AttendanceResource::make($attendance), 'Attendance updated successfully');
    }

    /**
     * Remove the specified Attendance from storage.
     * DELETE /attendances/{id}
     *
     * @param int $id
     *
     * @return Response
     * @throws \Exception
     *
     */
    public function destroy($id)
    {
        $this->authorize('attendance_delete');

        /** @var Attendance $attendance */
        $attendance = $this->attendanceRepository->find($id);

        if (empty($attendance)) {
            return $this->sendError('Attendance not found');
        }

        $attendance->delete();

        return $this->sendSuccess('Attendance deleted successfully');
    }
}
