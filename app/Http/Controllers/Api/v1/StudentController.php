<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\EnumAccountType;
use App\Http\Controllers\Controller;
use App\Http\Requests\api\StoreStudentRequest;
use App\Http\Requests\api\UpdateStudentRequest;
use App\Http\Resources\AccountMiniResource;
use App\Http\Resources\Student\StudentResource;
use App\Models\Account;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with('TeacherGroup')->latest()->get();
        return $this->SuccessfullyResponse(
            StudentResource::collection($students),
            __('general.loadSuccess')
        );
    }
    public function indexLite()
    {
        $students = Student::latest()->get();
        return $this->SuccessfullyResponse(
            AccountMiniResource::collection($students),
            __('general.loadSuccess')
        );
    }

    public function store(StoreStudentRequest $request)
    {
        $validated = $request->validated();
        $account = Account::create([
            'name' => $validated['name'],
            'account_type_id' => EnumAccountType::Student->value,
            'parent_id' => EnumAccountType::Student->value,
            'guide' => $validated['guide'],
        ]);
        $student = Student::create([
            'account_id' => $account->id,
            'phone' => $validated['phone'],
            'school_name' => $validated['school_name'],
            'parent_phone' => $validated['parent_phone'],
            'parent_phone2' => $validated['parent_phone2'],
            'gender' => $validated['gender'],
            'user_input_id' => $validated['user_input_id'],
            'user_created_id' => Auth::id(),
            'user_updated_id' => Auth::id(),
        ]);
        return $this->SuccessfullyResponse(
            new StudentResource($student),
            __('general.createSuccess')
        );
    }

    public function show($account_id)
    {
        $student = Student::where('account_id', $account_id)->first();
        if (!$student) {
            return $this->FailedResponse(__('general.notFound'));
        }
        return $this->SuccessfullyResponse(
            new StudentResource($student->load('TeacherGroup')),
            __('general.loadSuccess')
        );
    }

    public function update(UpdateStudentRequest $request, $account_id)
    {
        $student = Student::where('account_id', $account_id)->first();
        if (!$student) {
            return $this->FailedResponse(__('general.notFound'));
        }
        $account = Account::find($student->account_id);
        $account->update([
            'name' => $request->name,
        ]);
        $validated = $request->validated();
        $student->update([
            'phone' => $validated['phone'],
            'parent_phone' => $validated['parent_phone'],
            'parent_phone2' => $validated['parent_phone2'] ?? null,
            'school_name' => $validated['school_name'],
            'gender' => $validated['gender'],
            'user_input_id' => $validated['user_input_id'],
            'user_updated_id' => Auth::id(),
        ]);
        return $this->SuccessfullyResponse(
            new StudentResource($student->load('TeacherGroup')),
            __('general.updateSuccess')
        );
    }

    public function destroy($account_id)
    {
        $student = Student::where('account_id', $account_id)->first();
        if (!$student) {
            return $this->FailedResponse(__('general.notFound'));
        }
        $student->delete();
        return $this->SuccessfullyResponse(null, __('general.deleteSuccess'));
    }

    public function assignTeacherGroup(Request $request, $account_id)
    {
        $request->validate([
            'teacher_group_id' => 'required|exists:teacher_groups,id',
        ]);

        $student = Student::where('account_id', $account_id)->first();
        if (!$student) {
            return $this->FailedResponse(__('general.notFound'));
        }

        $student->update([
            'teacher_group_id' => $request->teacher_group_id,
        ]);

        return $this->SuccessfullyResponse(
            new StudentResource($student->load('TeacherGroup')),
            __('general.updateSuccess')
        );
    }
}
