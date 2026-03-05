<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\StoreServiceRequest;
use App\Http\Requests\api\UpdateServiceRequest;
use App\Http\Resources\Service\ServiceResource;
use App\Models\Service;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::latest()->get();
        return $this->SuccessfullyResponse(
            ServiceResource::collection($services),
            __('general.loadSuccess')
        );
    }

    public function store(StoreServiceRequest $request)
    {
        $validated = $request->validated();

        $iconPath = null;
        if ($request->hasFile('icon')) {
            $iconPath = $request->file('icon')->store('services', 'public');
        }

        $service = Service::create([
            'name' => $validated['name'],
            'icon' => $iconPath,
        ]);

        return $this->SuccessfullyResponse(
            new ServiceResource($service),
            __('general.createSuccess')
        );
    }

    public function show($id)
    {
        $service = Service::find($id);
        if (!$service) {
            return $this->FailedResponse(__('general.notFound'), 404);
        }
        return $this->SuccessfullyResponse(
            new ServiceResource($service),
            __('general.loadSuccess')
        );
    }

    public function update(UpdateServiceRequest $request, $id)
    {
        $service = Service::find($id);
        if (!$service) {
            return $this->FailedResponse(__('general.notFound'), 404);
        }

        $validated = $request->validated();

        // Handle new icon upload
        if ($request->hasFile('icon')) {
            // Delete old icon if exists
            if ($service->icon) {
                Storage::disk('public')->delete($service->icon);
            }
            $validated['icon'] = $request->file('icon')->store('services', 'public');
        }

        $service->update($validated);

        return $this->SuccessfullyResponse(
            new ServiceResource($service),
            __('general.updateSuccess')
        );
    }

    public function destroy($id)
    {
        $service = Service::find($id);
        if (!$service) {
            return $this->FailedResponse(__('general.notFound'), 404);
        }

        // Delete icon file if exists
        if ($service->icon) {
            Storage::disk('public')->delete($service->icon);
        }

        $service->delete();

        return $this->SuccessfullyResponse(null, __('general.deleteSuccess'));
    }
}
