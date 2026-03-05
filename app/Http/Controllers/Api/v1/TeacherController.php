<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\EnumAccountType;
use App\Http\Controllers\Controller;
use App\Http\Requests\api\StoreTeacherRequest;
use App\Http\Requests\api\UpdateTeacherRequest;
use App\Http\Resources\Teacher\TeacherLiteResource;
use App\Http\Resources\Teacher\TeacherResource;
use App\Http\Resources\TeacherGroup\TeacherGroupLiteResource;
use App\Models\Account;
use App\Models\Teacher;
use App\Models\TeacherGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = Teacher::get();
        return $this->SuccessfullyResponse(
            TeacherResource::collection($teachers),
            __('general.loadSuccess')
        );
    }
    public function indexLite()
    {
        $teachers = Teacher::get();
        return $this->SuccessfullyResponse(
            TeacherLiteResource::collection($teachers),
            __('general.loadSuccess')
        );
    }

    public function store(StoreTeacherRequest $request)
    {
        $validated = $request->validated();
        $account = Account::create([
            'name' => $validated['name'],
            'account_type_id' => EnumAccountType::Teacher->value,
            'parent_id' => EnumAccountType::Teacher->value,
            'guide' => $validated['guide'],
        ]);
        $validated['account_id'] = $account->id;
        Account::create([
            'name' => "صندوق " . $validated['name'],
            'account_type_id' => EnumAccountType::Box->value,
            'parent_id' => EnumAccountType::Box->value,
            'guide' => $validated['guide'] . rand(111, 999),
        ]);
        $teacher = Teacher::create([
            'account_id' => $account->id,
            'phone' => $validated['phone'],
            'institute_percentage' => $validated['institute_percentage'],
            'user_input_id' => $validated['user_input_id'],
            'user_created_id' => Auth::id(),
            'user_updated_id' => Auth::id(),
        ]);
        if ($request->has('study_material')) {
            $materials = is_string($request->study_material) ? json_decode($request->study_material, true) : $request->study_material;
            $syncData = [];
            foreach ($materials as $materialId) {
                $syncData[$materialId] = ['is_active' => 1];
            }
            $teacher->StudyMaterials()->sync($syncData);
        }

        $arrayDetails = json_decode($request->teacher_group, true);
        $arrayItemInsert = [];
        foreach ($arrayDetails as $key => $item) {
            $newDetails = new TeacherGroup();
            $newDetails->teacher_id = $account->id;
            $newDetails->group_name = $item['groupName'];
            $newDetails->group_day = $item['groupDay'];
            $newDetails->group_time_from = $item['groupTimeFrom'];
            $newDetails->group_time_to = $item['groupTimeTo'];
            $newDetails->user_input_id = $teacher->user_input_id;
            $newDetails->user_created_id = Auth::id();
            $newDetails->user_updated_id = Auth::id();
            array_push($arrayItemInsert, $newDetails);
        }
        $teacher->TeacherGroups()->saveMany($arrayItemInsert);

        return $this->SuccessfullyResponse(
            new TeacherResource($teacher),
            __('general.createSuccess')
        );
    }

    public function show($account_id)
    {
        $teacher = Teacher::where('account_id', $account_id)->first();
        if (!$teacher) {
            return $this->FailedResponse(__('general.notFound'));
        }
        return $this->SuccessfullyResponse(
            new TeacherResource($teacher),
            __('general.loadSuccess')
        );
    }

    public function update(UpdateTeacherRequest $request, $account_id)
    {
        $teacher = Teacher::where('account_id', $account_id)->first();
        $account = Account::find($account_id);
        $account->update([
            'name' => $request->name,
        ]);
        $arrayDetails = json_decode($request->teacher_group, true);
        $arrayItemInsert = [];
        foreach ($arrayDetails as $key => $item) {
            if ($item['id'] > 0) {
                $newDetails = TeacherGroup::find($item['id']);
                $newDetails->teacher_id = $account->id;
                $newDetails->group_name = $item['groupName'];
                $newDetails->group_day = $item['groupDay'];
                $newDetails->group_time_from = $item['groupTimeFrom'];
                $newDetails->group_time_to = $item['groupTimeTo'];
                $newDetails->user_input_id = $teacher->user_input_id;
                $newDetails->user_updated_id = Auth::id();
                $newDetails->save();
            } else {
                $newDetails = new TeacherGroup();
                $newDetails->teacher_id = $account->id;
                $newDetails->group_name = $item['groupName'];
                $newDetails->group_day = $item['groupDay'];
                $newDetails->group_time_from = $item['groupTimeFrom'];
                $newDetails->group_time_to = $item['groupTimeTo'];
                $newDetails->user_input_id = $teacher->user_input_id;
                $newDetails->user_created_id = Auth::id();
                $newDetails->user_updated_id = Auth::id();
                array_push($arrayItemInsert, $newDetails);
            }
        }
        if (count($arrayItemInsert) > 0) {
            $teacher->TeacherGroups()->saveMany($arrayItemInsert);
        }

        if ($request->has('study_material')) {
            $materials = is_string($request->study_material) ? json_decode($request->study_material, true) : $request->study_material;
            $syncData = [];
            foreach ($materials as $materialId) {
                $syncData[$materialId] = ['is_active' => 1];
            }
            $teacher->StudyMaterials()->sync($syncData);
        }

        $teacher->update([
            'phone' => $request->phone,
            'institute_percentage' => $request->institute_percentage,
            'user_input_id' => $request->user_input_id,
            'user_updated_id' => Auth::id(),
        ]);
        return $this->SuccessfullyResponse(
            new TeacherResource($teacher),
            __('general.updateSuccess')
        );
    }
    public function destroyDetails(string $id)
    {
        $data = TeacherGroup::find($id);
        $data->delete();
    }
    public function destroy($account_id)
    {
        $teacher = Teacher::where('account_id', $account_id)->first();
        if (!$teacher) {
            return $this->FailedResponse(__('general.notFound'));
        }
        $teacher->delete();
        TeacherGroup::where('teacher_id', $account_id)->delete();
        return $this->SuccessfullyResponse(null, __('general.deleteSuccess'));
    }
    public function assignMaterial(Request $request)
    {
        $teacher = Teacher::where('account_id', $request->account_id)->first();
        if (!$teacher) {
            return $this->FailedResponse(__('general.notFound'));
        }
        $teacher->StudyMaterials()->syncWithoutDetaching($request->study_material_id);

        return $this->SuccessfullyResponse(
            null,
            __('general.updateSuccess')
        );
    }

    public function detachMaterial(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'study_material_id' => 'required|exists:study_materials,id',
        ]);

        $teacher = Teacher::where('account_id', $request->account_id)->first();
        if (!$teacher) {
            return $this->FailedResponse(__('general.notFound'));
        }
        $teacher->StudyMaterials()->detach($request->study_material_id);

        return $this->SuccessfullyResponse(
            null,
            __('general.deleteSuccess')
        );
    }

    public function getTeachersByMaterial($study_material_id)
    {
        $teachers = Teacher::whereHas('StudyMaterials', function ($query) use ($study_material_id) {
            $query->where('study_material_id', $study_material_id)
                ->where('study_material_teacher.is_active', 1);
        })->get();

        return $this->SuccessfullyResponse(
            TeacherLiteResource::collection($teachers),
            __('general.loadSuccess')
        );
    }

    public function getGroupsByTeacher($account_id)
    {
        $groups = TeacherGroup::where('teacher_id', $account_id)->get();

        return $this->SuccessfullyResponse(
            TeacherGroupLiteResource::collection($groups),
            __('general.loadSuccess')
        );
    }
}
