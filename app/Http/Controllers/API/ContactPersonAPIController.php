<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateContactPersonAPIRequest;
use App\Http\Requests\API\UpdateContactPersonAPIRequest;
use App\Http\Resources\ContactPersonResource;
use App\Models\Client;
use App\Models\ContactPerson;
use App\Repositories\ContactPersonRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ContactPersonAPIController
 */
class ContactPersonAPIController extends AppBaseController
{
    private ContactPersonRepository $contactPersonRepository;

    public function __construct(ContactPersonRepository $contactPersonRepo)
    {
        $this->contactPersonRepository = $contactPersonRepo;
    }

    /**
     * Display a listing of the ContactPeople.
     * GET|HEAD /contact-people
     */
    public function index(Request $request): JsonResponse
    {
        list($input, $current, $pageSize) = $this->getInput($request);

        $checkEmpty = $request->all();
        if (empty($checkEmpty)) {
            return response()->json($this->contactPersonRepository->all([
                'status' => ContactPerson::ACTIVE,
            ])->pluck('contact_name', 'id'));
        }

        $contactPerson = $this->contactPersonRepository->all(
            $input,
            $current,
            $pageSize
        );

        $total = count($this->contactPersonRepository->all($input));

        return $this->sendResponse([
            'data'  => ContactPersonResource::collection($contactPerson),
            'total' => $total,
        ], 'Clients retrieved successfully');

        // $contactPeople = $this->contactPersonRepository->all(
        //     $request->except(['skip', 'limit']),
        //     $request->get('skip'),
        //     $request->get('limit')
        // );

        // return $this->sendResponse($contactPeople->toArray(), 'Contact People retrieved successfully');
    }

    /**
     * Store a newly created ContactPerson in storage.
     * POST /contact-people
     */
    public function store(Request $request): JsonResponse
    {
        $input = $request->all();

        if (isset($request->contact_people)) {
            foreach ($input['contact_people'] as $contactPerson) {
                $contactPerson = ContactPerson::create([
                    'client_id'    => $input['client_id'],
                    'contact_name' => $contactPerson['contact_name'] ?? null,
                    'phone'        => $contactPerson['phone'] ?? null,
                    'general_line' => $contactPerson['general_line'] ?? null,
                    'direct_line'  => $contactPerson['direct_line'] ?? null,
                    'whatsapp'     => $contactPerson['whatsapp'] ?? null,
                    'fax'          => $contactPerson['fax'] ?? null,
                    'status'       => $contactPerson['status'] ?? 1,
                    'address'      => $contactPerson['address'] ?? null,
                    'remark'       => $contactPerson['remark'] ?? null,
                ]);
            }
        }

        return $this->sendResponse(Client::find($input['client_id'])->contactPeople->toArray(), 'Contact Person saved successfully');
    }

    /**
     * Display the specified ContactPerson.
     * GET|HEAD /contact-people/{id}
     */
    public function show($id): JsonResponse
    {
        /** @var ContactPerson $contactPerson */
        $contactPerson = $this->contactPersonRepository->find($id);

        if (empty($contactPerson)) {
            return $this->sendError('Contact Person not found');
        }

        return $this->sendResponse($contactPerson->toArray(), 'Contact Person retrieved successfully');
    }

    /**
     * Update the specified ContactPerson in storage.
     * PUT/PATCH /contact-people/{id}
     */
    public function update($id, UpdateContactPersonAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var ContactPerson $contactPerson */
        $contactPerson = $this->contactPersonRepository->find($id);

        if (empty($contactPerson)) {
            return $this->sendError('Contact Person not found');
        }

        $contactPerson = $this->contactPersonRepository->update($input, $id);

        return $this->sendResponse($contactPerson->toArray(), 'ContactPerson updated successfully');
    }

    /**
     * Remove the specified ContactPerson from storage.
     * DELETE /contact-people/{id}
     *
     * @throws \Exception
     */
    public function destroy($id): JsonResponse
    {
        /** @var ContactPerson $contactPerson */
        $contactPerson = $this->contactPersonRepository->find($id);

        if (empty($contactPerson)) {
            return $this->sendError('Contact Person not found');
        }

        $contactPerson->delete();

        return $this->sendSuccess('Contact Person deleted successfully');
    }
}
