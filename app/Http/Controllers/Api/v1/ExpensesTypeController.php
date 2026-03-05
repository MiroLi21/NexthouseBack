<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ExpensesTypeRequest;
use App\Http\Resources\Voucher\ExpensesTypeResource;
use App\Models\ExpensesType;
use Illuminate\Support\Facades\Auth;

class ExpensesTypeController extends Controller
{
    public function index()
    {
        $expensesTypes = ExpensesType::all();

        return $this->SuccessfullyResponse(
            ExpensesTypeResource::collection($expensesTypes),
            __('general.loadSuccess')
        );
    }

    public function store(ExpensesTypeRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();

        $expensesType = ExpensesType::create($validated);

        return $this->SuccessfullyResponse(
            new ExpensesTypeResource($expensesType),
            __('general.createSuccess')
        );
    }

    public function show($id)
    {
        $expensesType = ExpensesType::find($id);

        if (!$expensesType) {
            return $this->FailedResponse(__('general.notFound'));
        }

        return $this->SuccessfullyResponse(
            new ExpensesTypeResource($expensesType),
            __('general.loadSuccess')
        );
    }

    public function update(ExpensesTypeRequest $request, $id)
    {
        $expensesType = ExpensesType::find($id);

        if (!$expensesType) {
            return $this->FailedResponse(__('general.notFound'));
        }

        $validated = $request->validated();
        $expensesType->update($validated);

        return $this->SuccessfullyResponse(
            new ExpensesTypeResource($expensesType),
            __('general.updateSuccess')
        );
    }

    public function destroy($id)
    {
        $expensesType = ExpensesType::find($id);

        if (!$expensesType) {
            return $this->FailedResponse(__('general.notFound'));
        }

        $expensesType->delete();

        return $this->SuccessfullyResponse(
            null,
            __('general.deleteSuccess')
        );
    }
}
