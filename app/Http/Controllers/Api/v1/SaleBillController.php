<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\StoreSaleBillRequest;
use App\Http\Requests\api\UpdateSaleBillRequest;
use App\Http\Resources\SaleBill\SaleBillResource;
use App\Models\SaleBill;
use App\Models\SaleDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SaleBillController extends Controller
{
    public function index()
    {
        $saleBills = SaleBill::with(['Student.Account', 'SaleDetails.Teacher.Account'])->latest()->get();
        return $this->SuccessfullyResponse(
            SaleBillResource::collection($saleBills),
            __('general.loadSuccess')
        );
    }

    public function getByStudent($student_id)
    {
        $saleBills = SaleBill::with(['Student.Account', 'SaleDetails.Teacher.Account'])->where('account_student_id', $student_id)->latest()->get();
        return $this->SuccessfullyResponse(
            SaleBillResource::collection($saleBills),
            __('general.loadSuccess')
        );
    }

    public function store(StoreSaleBillRequest $request)
    {
        $validated = $request->validated();
        $validated += [
            'user_updated_id' => Auth::id(),
            'user_created_id' => Auth::id(),
        ];
        $saleBill = SaleBill::create($validated);

        $arrayDetails = json_decode($request->sale_details, true);
        $arrayItemInsert = [];
        foreach ($arrayDetails as $key => $item) {
            $newDetails = new SaleDetails();
            $newDetails->sale_bill_id = $saleBill->id;
            $newDetails->study_material_id = $item['StudyMaterial']['id'];
            $newDetails->account_teacher_id = $item['AccountTeacher']['Account']['id'];
            $newDetails->teacher_group_id = $item['TeacherGroup']['id'];
            $newDetails->pay_type_id = $item['PayType']['id'];
            $newDetails->price = $item['price'];
            $newDetails->paid = $item['paid'];
            $newDetails->remaining = $item['remaining'];
            $newDetails->note = $item['note'];
            array_push($arrayItemInsert, $newDetails);
        }
        $saleBill->SaleDetails()->saveMany($arrayItemInsert);
        Log::alert($arrayDetails);
        Log::alert($saleBill);
        // $fc = new FinancialConstraintController();
        // $fc->sale($saleBill);
        return $this->SuccessfullyResponse(
            new SaleBillResource($saleBill->load(['Student.Account', 'SaleDetails.Teacher.Account'])),
            __('general.createSuccess')
        );
    }

    public function show($id)
    {
        $saleBill = SaleBill::find($id);
        if (!$saleBill) {
            return $this->FailedResponse(__('general.notFound'));
        }
        return $this->SuccessfullyResponse(
            new SaleBillResource($saleBill->load(['Student.Account', 'SaleDetails.Teacher.Account'])),
            __('general.loadSuccess')
        );
    }

    public function update(UpdateSaleBillRequest $request, $id)
    {
        $saleBill = SaleBill::find($id); //->with([]);

        $arrayDetails = json_decode($request->sale_details, true);
        $arrayItemInsert = [];
        foreach ($arrayDetails as $key => $item) {
            if ($item['id'] > 0) {
                $newDetails = SaleDetails::find($item['id']);
                $newDetails->sale_bill_id = $saleBill->id;
                $newDetails->study_material_id = $item['StudyMaterial']['id'];
                $newDetails->account_teacher_id = $item['AccountTeacher']['Account']['id'];
                $newDetails->teacher_group_id = $item['TeacherGroup']['id'];
                $newDetails->pay_type_id = $item['PayType']['id'];
                $newDetails->price = $item['price'];
                $newDetails->paid = $item['paid'];
                $newDetails->remaining = $item['remaining'];
                $newDetails->note = $item['note'];
                $newDetails->save();
            } else {
                $newDetails = new SaleDetails();
                $newDetails->sale_bill_id = $saleBill->id;
                $newDetails->study_material_id = $item['StudyMaterial']['id'];
                $newDetails->account_teacher_id = $item['AccountTeacher']['Account']['id'];
                $newDetails->teacher_group_id = $item['TeacherGroup']['id'];
                $newDetails->pay_type_id = $item['PayType']['id'];
                $newDetails->price = $item['price'];
                $newDetails->paid = $item['paid'];
                $newDetails->remaining = $item['remaining'];
                $newDetails->note = $item['note'];
                array_push($arrayItemInsert, $newDetails);
            }
        }
        if (count($arrayItemInsert) > 0) {
            $saleBill->SaleDetails()->saveMany($arrayItemInsert);
        }

        // Log::alert($saleBill);
        // Log::alert($arrayDetails);
        $fc = new FinancialConstraintController();
        $fc->sale($saleBill, SaleBill::class);
        $saleBill->update($request->validated());
        return $this->SuccessfullyResponse(
            new SaleBillResource($saleBill->load(['Student.Account', 'SaleDetails.Teacher.Account'])),
            __('general.updateSuccess')
        );
    }
    public function destroyDetails(string $id)
    {
        $data = SaleDetails::find($id);
        $data->delete();
    }
    public function destroy($id)
    {
        $saleBill = SaleBill::find($id);
        if (!$saleBill) {
            return $this->FailedResponse(__('general.notFound'));
        }
        $saleBill->delete();
        SaleDetails::where('sale_bill_id', $saleBill->id)->delete();
        return $this->SuccessfullyResponse(null, __('general.deleteSuccess'));
    }
}
