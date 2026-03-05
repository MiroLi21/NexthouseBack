<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\v1\FinancialConstraintController;
use App\Http\Controllers\Controller;
use App\Http\DTO\FinancialConstraintDTO;
use App\Http\Requests\Api\ExpensesRequest;
use App\Http\Resources\Voucher\ExpensesIndexResource;
use App\Http\Resources\Voucher\ExpensesResource;
use App\Http\Resources\Voucher\ExpensesRowsResource;
use App\Http\Resources\Voucher\ExpensesTypeResource;
use App\Models\Expenses;
use App\Models\ExpensesRows;
use App\Models\ExpensesType;
use App\Traits\HasNavigation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExpensesController extends Controller
{
    use HasNavigation;

    protected $modelClass = Expenses::class;
    protected $resourceClass = ExpensesResource::class;
    protected $relations = ['ExpensesRows'];
    public function index()
    {
        $expenses = Expenses::with(['ExpensesRows.ExpensesType', 'ExpensesRows.Currency'])->get();
        return $this->SuccessfullyResponse(
            ExpensesIndexResource::collection($expenses),
            __('general.loadSuccess')
        );
    }
    public function get_expensesRows()
    {
        $expensesRows = ExpensesRows::get();

        return $this->SuccessfullyResponse(
            ExpensesRowsResource::collection($expensesRows),
            __('general.loadSuccess')
        );
    }
    public function get_expensesTypes()
    {
        $expensesTypes = ExpensesType::get();

        return $this->SuccessfullyResponse(
            ExpensesTypeResource::collection($expensesTypes),
            __('general.loadSuccess')
        );
    }
    public function store(ExpensesRequest $request)
    {
        $validated = $request->validated();
        $validated += ['user_create_id' => Auth::id()];
        $validated += ['user_update_id' => Auth::id()];
        $expense = Expenses::create($validated);
        $arrayRows = json_decode($request->expenses_rows, true);
        $arrayItemInsert = [];
        foreach ($arrayRows as $key => $item) {
            $newRows = new ExpensesRows();
            $newRows->expenses_id = $expense->id;
            $newRows->expenses_type_id = $item['ExpensesType']['id'];
            $newRows->money = $item['money'];
            $newRows->note = $item['note'] ?? "";
            $newRows->currency_id = $item['Currency']['id'];
            $newRows->exchange = $item['exchange'];
            $newRows->user_create_id = Auth::id();
            $newRows->user_update_id = Auth::id();
            $newRows->created_at = now();
            $newRows->updated_at = now();
            array_push($arrayItemInsert, $newRows);
        }
        $expense->ExpensesRows()->saveMany($arrayItemInsert);

        return $this->SuccessfullyResponse(
            new ExpensesResource($expense),
            __('general.createSuccess')
        );
    }


    public function update(ExpensesRequest $request, $id)
    {
        $validated = $request->validated();
        // $validated += ['user_update_id' => Auth::id()]; 
        $expenses = Expenses::find($id);
        $arrayRows = json_decode($request->expenses_rows, true);
        $arrayItemInsert = [];
        foreach ($arrayRows as $key => $item) {
            if ($item['id'] > 0) {
                $newRows = ExpensesRows::find($item['id']);
                $newRows->expenses_id = $expenses->id;
                $newRows->expenses_type_id = $item['ExpensesType']['id'];
                $newRows->money = $item['money'];
                $newRows->note = $item['note'] ?? null;
                $newRows->currency_id = $item['Currency']['id'];
                $newRows->exchange = $item['exchange'];
                $newRows->user_create_id = Auth::id();
                $newRows->created_at = now();
                $newRows->user_update_id = Auth::id();
                $newRows->updated_at = now();
                $newRows->save();
            } else {
                $newRows = new ExpensesRows();
                $newRows->expenses_id = $expenses->id;
                $newRows->expenses_type_id = $item['ExpensesType']['id'];
                $newRows->money = $item['money'];
                $newRows->note = $item['note'] ?? null;
                $newRows->currency_id = $item['Currency']['id'];
                $newRows->exchange = $item['exchange'];
                $newRows->user_create_id = Auth::id();
                $newRows->created_at = now();
                $newRows->user_update_id = Auth::id();
                $newRows->updated_at = now();
                array_push($arrayItemInsert, $newRows);
            }
        }
        if (count($arrayItemInsert) > 0) {
            $expenses->ExpensesRows()->saveMany($arrayItemInsert);
        }
        $expenses->update($validated);
    }

    public function show($id)
    {
        $expenses = Expenses::with(['ExpensesRows.ExpensesType', 'ExpensesRows.Currency'])->find($id);

        if (!$expenses) {
            return $this->FailedResponse(__('general.notFound'));
        }

        return $this->SuccessfullyResponse(
            new ExpensesResource($expenses),
            __('general.loadSuccess')
        );
    }
    public function destroy(string $id)
    {
        $data = Expenses::find($id);
        $data->ExpensesRows()->delete();
        $data->delete();
    }
    public function destroyRow(string $id)
    {
        $data = ExpensesRows::find($id);
        $data->delete();
    }
    public function recover($id)
    {
    }
}
