<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateRoleAPIRequest;
use App\Http\Requests\API\UpdateRoleAPIRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Repositories\RoleRepository;
use Illuminate\Http\Request;
use Response;

/**
 * Class RoleController
 * @package App\Http\Controllers\API
 */
class RoleAPIController extends AppBaseController
{
    /** @var  RoleRepository */
    private $roleRepository;

    public function __construct(RoleRepository $roleRepo)
    {
        $this->roleRepository = $roleRepo;
    }

    /**
     * Display a listing of the Role.
     * GET|HEAD /roles
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {

        list($input, $current, $pageSize) = $this->getInput($request);

        $checkEmpty = $request->all();
        if (empty($checkEmpty)) {
            return response()->json($this->roleRepository->all([
                'status' => Role::ACTIVE,
            ])->pluck('role_name', 'id'));
        }

        $this->authorize('role_view');

        if (isset($input['search'])) {
            $query = Role::where('role_name', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('code', 'LIKE', '%' . $input['search'] . '%');

            $total = $query->get()->count();

            $roles = $query->skip($current)
                ->take($pageSize)
                ->get();

        } else {

            $roles = $this->roleRepository->all(
                $input,
                $current,
                $pageSize
            );

            $total = count($this->roleRepository->all(
                $input
            ));
        }

        return $this->sendResponse([
            'data'  => RoleResource::collection($roles),
            'total' => $total,
        ], 'Roles retrieved successfully');
    }

    /**
     * Store a newly created Role in storage.
     * POST /roles
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $this->authorize('role_create');

        $input = $request->all();

        $role = $this->roleRepository->create($input);

        return $this->sendResponse(RoleResource::make($role), 'Role saved successfully');
    }

    /**
     * Display the specified Role.
     * GET|HEAD /roles/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $this->authorize('role_view');

        /** @var Role $role */
        $role = $this->roleRepository->find($id);

        if (empty($role)) {
            return $this->sendError('Role not found');
        }

        return $this->sendResponse(RoleResource::make($role), 'Role retrieved successfully');
    }

    /**
     * Update the specified Role in storage.
     * PUT/PATCH /roles/{id}
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $this->authorize('role_update');

        $input = $request->all();

        /** @var Role $role */
        $role = $this->roleRepository->find($id);

        if (empty($role)) {
            return $this->sendError('Role not found');
        }

        $role = $this->roleRepository->update($input, $id);

        return $this->sendResponse(RoleResource::make($role), 'Role updated successfully');
    }

    /**
     * Remove the specified Role from storage.
     * DELETE /roles/{id}
     *
     * @param int $id
     *
     * @return Response
     * @throws \Exception
     *
     */
    public function destroy($id)
    {
        $this->authorize('role_delete');

        /** @var Role $role */
        $role = $this->roleRepository->find($id);

        if (empty($role)) {
            return $this->sendError('Role not found');
        }

        $role->delete();

        return $this->sendSuccess('Role deleted successfully');
    }
}
