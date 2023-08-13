<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\Mobile\EmployeeResource;
use App\Models\Employee;
use App\Repositories\EmployeeRepository;
use Illuminate\Http\Request;
use Response;

/**
 * Class EmployeeController
 * @package App\Http\Controllers\API
 */
class EmployeeAPIController extends AppBaseController
{
    /** @var  EmployeeRepository */
    private $employeeRepository;

    public function __construct(EmployeeRepository $employeeRepo)
    {
        $this->employeeRepository = $employeeRepo;
    }

    /**
     * Display the specified Employee.
     * GET|HEAD /employees/{id}
     *
     * @return Response
     */
    public function self()
    {
        /** @var Employee $employee */

        $employee = $this->employeeRepository->find(auth()->user()->id, ['roles']);

        if (empty($employee)) {
            return $this->sendError('Employee not found');
        }

        return $this->sendResponse(EmployeeResource::make($employee), 'Employee retrieved successfully');
    }

    public function changePassword(Request $request)
    {
        $input = $request->all();

        $user = auth('employee')->user();

        $user->update([
            'password' => bcrypt($input['password']),
        ]);

        $user->refresh();

        return $this->sendResponse(EmployeeResource::make($user), 'Employee retrieved successfully');
    }
}