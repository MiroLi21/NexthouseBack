<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\StoreStudyMaterialRequest;
use App\Http\Requests\api\UpdateStudyMaterialRequest;
use App\Http\Resources\GeneralIdNameResource;
use App\Http\Resources\StudyMaterial\StudyMaterialResource;
use App\Models\StudyMaterial;
use Illuminate\Support\Facades\Auth;

class StudyMaterialController extends Controller
{
    public function index()
    {
        $studyMaterials = StudyMaterial::latest()->get();
        return $this->SuccessfullyResponse(
            StudyMaterialResource::collection($studyMaterials),
            __('general.loadSuccess')
        );
    }
    public function indexLite()
    {
        $studyMaterials = StudyMaterial::latest()->select('id', 'name')->get();
        return $this->SuccessfullyResponse(
            GeneralIdNameResource::collection($studyMaterials),
            __('general.loadSuccess')
        );
    }

    public function store(StoreStudyMaterialRequest $request)
    {
        $validated = $request->validated();
        $validated += [
            'user_updated_id' => Auth::id(),
            'user_created_id' => Auth::id(),
        ];
        $studyMaterial = StudyMaterial::create($validated);
        return $this->SuccessfullyResponse(
            new StudyMaterialResource($studyMaterial),
            __('general.createSuccess')
        );
    }

    public function show($id)
    {
        $studyMaterial = StudyMaterial::find($id);
        if (!$studyMaterial) {
            return $this->FailedResponse(__('general.notFound'));
        }
        return $this->SuccessfullyResponse(
            new StudyMaterialResource($studyMaterial),
            __('general.loadSuccess')
        );
    }

    public function update(UpdateStudyMaterialRequest $request, $id)
    {
        $studyMaterial = StudyMaterial::find($id);
        if (!$studyMaterial) {
            return $this->FailedResponse(__('general.notFound'));
        }
        $studyMaterial->update($request->validated());
        return $this->SuccessfullyResponse(
            new StudyMaterialResource($studyMaterial),
            __('general.updateSuccess')
        );
    }

    public function destroy($id)
    {
        $studyMaterial = StudyMaterial::find($id);
        if (!$studyMaterial) {
            return $this->FailedResponse(__('general.notFound'));
        }
        $studyMaterial->delete();
        return $this->SuccessfullyResponse(null, __('general.deleteSuccess'));
    }
}
