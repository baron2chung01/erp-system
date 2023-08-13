<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateClientAPIRequest;
use App\Http\Requests\API\UpdateClientAPIRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Models\ContactPerson;
use App\Repositories\ClientRepository;
use App\Traits\Arr;
use Illuminate\Http\Request;
use Response;

/**
 * Class ClientController
 * @package App\Http\Controllers\API
 */
class ClientAPIController extends AppBaseController
{
    /** @var  ClientRepository */
    private $clientRepository;

    public function __construct(ClientRepository $clientRepo)
    {
        $this->clientRepository = $clientRepo;
    }

    /**
     * Display a listing of the Client.
     * GET|HEAD /clients
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->authorize('client_view');

        list($input, $current, $pageSize) = $this->getInput($request);

        $checkEmpty = $request->all();
        if (empty($checkEmpty)) {
            return response()->json($this->clientRepository->all([
                'status' => Client::ACTIVE,
            ])->pluck('display_name', 'id'));
        }

        if (isset($input['search'])) {
            $query = Client::where('code', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('name', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('address', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('phone', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('contact_name', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('email', 'LIKE', '%' . $input['search'] . '%');

            $total = $query->get()->count();

            $clients = $query->skip($current)
                ->limit($pageSize)
                ->get();

        } else {

            $clients = $this->clientRepository->all(
                $input,
                $current,
                $pageSize
            );

            $total = count($this->clientRepository->all($input));
        }

        return $this->sendResponse([
            'data'  => ClientResource::collection($clients),
            'total' => $total,
        ], 'Clients retrieved successfully');
    }

    /**
     * Store a newly created Client in storage.
     * POST /clients
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $this->authorize('client_create');

        $input = $request->all();

        $input = Arr::underscoreKeys($input);

        $client = $this->clientRepository->create($input);

        return $this->sendResponse(ClientResource::make($client), 'Client saved successfully');
    }

    /**
     * Display the specified Client.
     * GET|HEAD /clients/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $this->authorize('client_view');

        /** @var Client $client */
        $client = $this->clientRepository->find($id);

        if (empty($client)) {
            return $this->sendError('Client not found');
        }

        return $this->sendResponse(ClientResource::make($client), 'Client retrieved successfully');
    }

    /**
     * Update the specified Client in storage.
     * PUT/PATCH /clients/{id}
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $this->authorize('client_update');

        $input = $request->all();

        /** @var Client $client */
        $client = $this->clientRepository->find($id);

        if (empty($client)) {
            return $this->sendError('Client not found');
        }

        if (isset($request->contact_people)) {
            $oriContactPeople = $client->contactPeople->pluck('id')->toArray();
            $inputContactPeople = array_map(function ($item) {
                if (isset($item['id'])) {
                    return $item['id'];
                }
                return null;
            }, $input['contact_people']);

            $inputContactPeople = array_filter($inputContactPeople);

            $deleteContactPeople = array_diff($oriContactPeople, $inputContactPeople);

            foreach ($deleteContactPeople as $deleteId) {
                ContactPerson::find($deleteId)->forceDelete();
            }

            foreach ($input['contact_people'] as $contactPerson) {
                if (isset($contactPerson['id'])) {
                    if (in_array($contactPerson['id'], $oriContactPeople)) {
                        ContactPerson::find($contactPerson['id'])->update([
                            'contact_name' => $contactPerson['contact_name'] ?? null,
                            'phone'        => $contactPerson['phone'] ?? null,
                            'general_line' => $contactPerson['general_line'] ?? null,
                            'direct_line'  => $contactPerson['direct_line'] ?? null,
                            'whatsapp'     => $contactPerson['whatsapp'] ?? null,
                            'fax'          => $contactPerson['fax'] ?? null,
                            'status'       => $contactPerson['status'] ?? 1,
                            'address'      => $contactPerson['address'] ?? null,
                            'job_title'    => $contactPerson['job_title'] ?? null,
                            'email'        => $contactPerson['email'] ?? null,
                            'remark'       => $contactPerson['remark'] ?? null,
                        ]);
                    }

                } else {
                    ContactPerson::create([
                        'client_id'    => $contactPerson['client_id'],
                        'contact_name' => $contactPerson['contact_name'] ?? null,
                        'phone'        => $contactPerson['phone'] ?? null,
                        'general_line' => $contactPerson['general_line'] ?? null,
                        'direct_line'  => $contactPerson['direct_line'] ?? null,
                        'whatsapp'     => $contactPerson['whatsapp'] ?? null,
                        'fax'          => $contactPerson['fax'] ?? null,
                        'status'       => $contactPerson['status'] ?? 1,
                        'address'      => $contactPerson['address'] ?? null,
                        'job_title'    => $contactPerson['job_title'] ?? null,
                        'email'        => $contactPerson['email'] ?? null,
                        'remark'       => $contactPerson['remark'] ?? null,
                    ]);
                }
            }
        }

        $client = $this->clientRepository->update($input, $id);

        return $this->sendResponse(ClientResource::make($client), 'Client updated successfully');
    }

    /**
     * Remove the specified Client from storage.
     * DELETE /clients/{id}
     *
     * @param int $id
     *
     * @return Response
     * @throws \Exception
     *
     */
    public function destroy($id)
    {
        $this->authorize('client_delete');

        /** @var Client $client */
        $client = $this->clientRepository->find($id);

        if (empty($client)) {
            return $this->sendError('Client not found');
        }

        $client->delete();

        return $this->sendSuccess('Client deleted successfully');
    }

    public function showContactByClient($clientId)
    {
        $client = Client::find($clientId);

        if (!isset($client)) {
            return $this->sendError('Client not found.');
        }

        $contactPeople = $client->contactPeople;

        if (isset($contactPeople)) {
            return response()->json($contactPeople->pluck('contact_name', 'id'));
        } else {
            return response()->json([]);
        }
    }

    public function duplicate($clientId)
    {
        // Get the client record to duplicate
        $client = Client::findOrFail($clientId);

        // Duplicate the client record
        $newClient = $client->replicate();
        $newClient->save();

        // Duplicate the associated contact people
        foreach ($client->contactPeople as $contactPerson) {
            $newContactPerson = $contactPerson->replicate();
            $newContactPerson->client_id = $newClient->id;
            $newContactPerson->save();
        }

        return $this->sendResponse(ClientResource::make($client), 'Client duplicated successfully');
    }
}
