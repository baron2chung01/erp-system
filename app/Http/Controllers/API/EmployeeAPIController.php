<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateEmployeeAPIRequest;
use App\Http\Requests\API\UpdateEmployeeAPIRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Models\EmployeeHasRole;
use App\Models\Role;
use App\Repositories\EmployeeRepository;
use App\Traits\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
     * Display a listing of the Employee.
     * GET|HEAD /employees
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->authorize('employee_view');

        list($input, $current, $pageSize) = $this->getInput($request);

        $checkEmpty = $request->all();
        if (empty($checkEmpty)) {
            return response()->json($this->employeeRepository->all([
                'status' => Employee::ACTIVE,
            ])->pluck('display_name', 'id'));
        }

        if (isset($input['search'])) {
            $query = Employee::orWhere('first_name', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('last_name', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('chinese_name', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('employee_no', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('phone', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('email', 'LIKE', '%' . $input['search'] . '%');

            $total = $query->get()->count();

            $employees = $query->skip($current)
                ->take($pageSize)
                ->get();

        } else {
            $employees = $this->employeeRepository->all(
                $input,
                $current,
                $pageSize
            );

            $total = count($this->employeeRepository->all(
                $input
            ));
        }

        return $this->sendResponse([
            'data'         => EmployeeResource::collection($employees),
            'total'        => $total,
            'leader_count' => Employee::whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('role')
                    ->join('employees_has_roles', 'role.id', '=', 'employees_has_roles.role_id')
                    ->whereRaw('employees.id = employees_has_roles.employee_id')
                    ->where('code', 'Leader')
                    ->orderBy('role.updated_at', 'desc')
                    ->whereNull('role.deleted_at');
            })
                ->where('status', 1)
                ->count()
            ,
            'worker_count' => Employee::whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('role')
                    ->join('employees_has_roles', 'role.id', '=', 'employees_has_roles.role_id')
                    ->whereRaw('employees.id = employees_has_roles.employee_id')
                    ->where('code', 'Worker')
                    ->orderBy('role.updated_at', 'desc')
                    ->whereNull('role.deleted_at');
            })
                ->where('status', 1)
                ->count(),
            'subcon_count' => Employee::whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('role')
                    ->join('employees_has_roles', 'role.id', '=', 'employees_has_roles.role_id')
                    ->whereRaw('employees.id = employees_has_roles.employee_id')
                    ->where('code', 'Subcontractor')
                    ->orderBy('role.updated_at', 'desc')
                    ->whereNull('role.deleted_at');
            })
                ->where('status', 1)
                ->count(),
        ], 'Employees retrieved successfully');
    }

    /**
     * Store a newly created Employee in storage.
     * POST /employees
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(CreateEmployeeAPIRequest $request)
    {
        $this->authorize('employee_create');

        $input = $request->all();

        $input = Arr::underscoreKeys($input);

        $employee = $this->employeeRepository->create($input);

        // create role
        if (isset($input['roles_id'])) {
            EmployeeHasRole::create([
                'role_id'     => $input['roles_id'],
                'employee_id' => $employee->id,
                'status'      => EmployeeHasRole::ACTIVE,
            ]);

            // create employee no according to role and id
            $role = Role::find($input['roles_id']);

            $employee->update([
                'employee_no' => $role->role_name[0] . str_pad($employee->id, 4, '0', STR_PAD_LEFT),
            ]);
        }

        return $this->sendResponse(EmployeeResource::make($employee), 'Employee saved successfully');
    }

    /**
     * Display the specified Employee.
     * GET|HEAD /employees/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $this->authorize('employee_view');

        /** @var Employee $employee */
        $employee = $this->employeeRepository->find($id, ['attendances', 'roles', 'workOrders']);

        if (empty($employee)) {
            return $this->sendError('Employee not found');
        }

        return $this->sendResponse(EmployeeResource::make($employee), 'Employee retrieved successfully');
    }

    /**
     * Update the specified Employee in storage.
     * PUT/PATCH /employees/{id}
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, UpdateEmployeeAPIRequest $request)
    {
        $this->authorize('employee_update');

        $input = $request->all();

        $input = Arr::underscoreKeys($input);

        /** @var Employee $employee */
        $employee = $this->employeeRepository->find($id);

        if (empty($employee)) {
            return $this->sendError('Employee not found');
        }

        if (isset($input['roles_id'])) {
            $query = EmployeeHasRole::where('employee_id', $id);
            if ($query->exists()) {
                $query->first()->forceDelete();
            }

            EmployeeHasRole::create([
                'role_id'     => $input['roles_id'],
                'employee_id' => $employee->id,
                'status'      => EmployeeHasRole::ACTIVE,
            ]);

            // update employee no according to role and id

            $role = Role::find($input['roles_id']);

            $input['employee_no'] = $role->role_name[0] . str_pad($id, 4, '0', STR_PAD_LEFT);

        }
        if (isset($request->password)) {
            $input['password'] = bcrypt($input['password']);
        }

        $employee = $this->employeeRepository->update($input, $id);

        return $this->sendResponse(EmployeeResource::make($employee), 'Employee updated successfully');
    }

    /**
     * Remove the specified Employee from storage.
     * DELETE /employees/{id}
     *
     * @param int $id
     *
     * @return Response
     * @throws \Exception
     *
     */
    public function destroy($id)
    {
        $this->authorize('employee_delete');

        /** @var Employee $employee */
        $employee = $this->employeeRepository->find($id);

        if (empty($employee)) {
            return $this->sendError('Employee not found');
        }

        $employee->delete();

        return $this->sendSuccess('Employee deleted successfully');
    }
}
