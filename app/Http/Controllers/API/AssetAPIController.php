<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\AssetResource;
use App\Models\Asset;
use App\Repositories\AssetRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Response;

/**
 * Class AssetController
 * @package App\Http\Controllers\API
 */

class AssetAPIController extends AppBaseController
{
    /** @var  AssetRepository */
    private $assetRepository;

    public function __construct(AssetRepository $assetRepo)
    {
        $this->assetRepository = $assetRepo;
    }

    /**
     * Display a listing of the Asset.
     * GET|HEAD /assets
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $assets = $this->assetRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($assets->toArray(), 'Assets retrieved successfully');
    }

    /**
     * Store a newly created Asset in storage.
     * POST /assets
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {

        $input = $request->all();

        $asset = collect([]);

        if (isset($input['file'])) {

            $file = $request->file('file');
            $fileName = $request->file('file')->getClientOriginalName(); // use original file name

            $modelName = strtolower(str_replace("App\\Models\\", "", $input['related_type']));
            $path = $modelName . '-' . $input['related_type']::ASSET[$input['asset_type']];
            $filePath = public_path($path) . '/' . $fileName;

            // check if file with same name exists in the path
            if (file_exists($filePath)) {
                $extension = '.' . pathinfo($filePath, PATHINFO_EXTENSION);
                $newFileName = pathinfo($fileName, PATHINFO_FILENAME);
                $i = 1;
                while (file_exists(public_path($path) . '/' . $newFileName . '(' . $i . ')' . $extension)) {
                    $i++;
                }
                $fileName = $newFileName . ' (' . $i . ')' . $extension;
            }

            // move the file to the path with the new filename if necessary
            $file->move(public_path($path), $fileName);

            $asset = Asset::create([
                'related_type'  => $input['related_type'],
                'related_id'    => $input['related_id'],
                'asset_type'    => $input['asset_type'],
                'url'           => $path . '/' . $fileName,
                'resource_path' => public_path($path) . '/' . $fileName,
                'file_name'     => $fileName,
                'file_size'     => $file->getSize(),
                'status'        => $request->status ?? Asset::ACTIVE,
            ]);
        }

        return $this->sendResponse(new AssetResource($asset), 'Asset saved successfully');
    }

    /**
     * Display the specified Asset.
     * GET|HEAD /assets/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var Asset $asset */
        $asset = $this->assetRepository->find($id);

        if (empty($asset)) {
            return $this->sendError('Asset not found');
        }

        return $this->sendResponse($asset->toArray(), 'Asset retrieved successfully');
    }

    /**
     * Update the specified Asset in storage.
     * PUT/PATCH /assets/{id}
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $input = $request->all();

        /** @var Asset $asset */
        $asset = $this->assetRepository->find($id);

        if (empty($asset)) {
            return $this->sendError('Asset not found');
        }

        $asset = $this->assetRepository->update($input, $id);

        return $this->sendResponse($asset->toArray(), 'Asset updated successfully');
    }

    /**
     * Remove the specified Asset from storage.
     * DELETE /assets/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var Asset $asset */
        $asset = $this->assetRepository->find($id);

        if (empty($asset)) {
            return $this->sendError('Asset not found');
        }

        Storage::delete(str_replace("/application", "", $asset->resource_path));

        $asset->forceDelete();

        return $this->sendSuccess('Asset deleted successfully');
    }
}
