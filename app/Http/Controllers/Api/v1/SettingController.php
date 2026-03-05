<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Http\Requests\api\StoreSettingRequest;
use App\Http\Resources\Core\SettingResource;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->SuccessfullyResponse(SettingResource::collection(Setting::all()), __('general.loadSuccess')); // Retrieve all settings
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSettingRequest $request)
    {
        $setting = Setting::create($request->validated()); // Create a new setting
        return $this->SuccessfullyResponse(new SettingResource($setting), __('general.createSuccess')); // Return the created setting with a 201 status
    }

    /**
     * Display the specified resource.
     */
    public function show(Setting $setting)
    {
        return $this->SuccessfullyResponse(new SettingResource($setting), __('general.loadSuccess')); // Return the specified setting
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(StoreSettingRequest $request, Setting $setting)
    {
        $setting->update($request->validated()); // Update the specified setting
        return $this->SuccessfullyResponse(new SettingResource($setting), __('general.updateSuccess')); // Return the updated setting
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Setting $setting)
    {
        $setting->delete(); // Delete the specified setting
        return $this->SuccessfullyResponse(__('general.deleteSuccess')); // Return a 204 status for successful deletion
    }
}
