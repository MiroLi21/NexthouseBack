<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\v1\FinancialConstraintController;
use App\Http\Controllers\Controller;
use App\Http\DTO\FinancialConstraintDTO;
use App\Http\Requests\Api\ReceiptRequest;
use App\Http\Resources\Voucher\ReceiptResource;
use App\Models\Receipt;
use App\Models\ReceiptRows;
use App\Traits\HasNavigation;
use Illuminate\Support\Facades\Auth;

class ReceiptController extends Controller
{
    use HasNavigation;

    protected $modelClass = Receipt::class;
    protected $resourceClass = ReceiptResource::class;
    protected $relations = ['UserInput', 'ReceiptRows', 'Account'];

    private array $rowRelations = ['Box', 'Discount', 'Currency'];

    public function index()
    {
        $receipts = Receipt::with(['UserInput', 'ReceiptRows' => fn($q) => $q->with($this->rowRelations)])->get();

        return $this->SuccessfullyResponse(
            ReceiptResource::collection($receipts),
            __('general.loadSuccess')
        );
    }

    public function store(ReceiptRequest $request)
    {
        $validated = $request->validated();
        $validated += ['user_created_id' => Auth::id(), 'user_updated_id' => Auth::id()];
        $receipt = Receipt::create($validated);

        $arrayRows = json_decode($request->receipt_rows, true);
        $arrayItemInsert = [];
        foreach ($arrayRows as $item) {
            $newRows = new ReceiptRows();
            $newRows->receipt_id = $receipt->id;
            $newRows->box_id = $item['Box']['id'];
            $newRows->discount_id = 69;
            $newRows->money = $item['money'];
            $newRows->discount = $item['discount'];
            $newRows->note = $item['note'] ?? '';
            $newRows->currency_id = $item['Currency']['id'];
            $newRows->exchange = $item['exchange'];
            $newRows->user_create_id = Auth::id();
            $newRows->user_update_id = Auth::id();
            $newRows->created_at = now();
            $newRows->updated_at = now();
            $arrayItemInsert[] = $newRows;
        }
        $receipt->ReceiptRows()->saveMany($arrayItemInsert);

        // Process financial constraints for all rows
        $fc = new FinancialConstraintController();
        foreach ($arrayItemInsert as $row) {
            $DTOrow = new FinancialConstraintDTO(
                id: $row->id,
                model_name: ReceiptRows::class,
                currency_id: $row->currency_id,
                exchange: $row->exchange,
                date: $receipt->date,
                user_created_id: $row->user_create_id,
                user_input_id: $receipt->user_input_id,
                account_id: $receipt->account_id,
                debit: $row->money,
                credit: 0,
                note: $row->note
            );
            $fc->singleConstRow($DTOrow);
        }

        return $this->SuccessfullyResponse(
            new ReceiptResource($receipt->load(['UserInput', 'ReceiptRows' => fn($q) => $q->with($this->rowRelations)])),
            __('general.createSuccess')
        );
    }

    public function show($id)
    {
        $receipt = Receipt::find($id);
        if (!$receipt) {
            return $this->FailedResponse(__('general.notFound'));
        }
        return $this->SuccessfullyResponse(
            new ReceiptResource($receipt->load(['UserInput', 'ReceiptRows' => fn($q) => $q->with($this->rowRelations)])),
            __('general.loadSuccess')
        );
    }

    public function update(ReceiptRequest $request, $id)
    {
        $receipt = Receipt::find($id);
        if (!$receipt) {
            return $this->FailedResponse(__('general.notFound'));
        }

        $validated = $request->validated();
        $validated['user_updated_id'] = Auth::id();
        $receipt = Receipt::find($id);
        $arrayRows = json_decode($request->receipt_rows, true);
        $arrayItemInsert = [];
        foreach ($arrayRows as $key => $item) {
            if ($item['id'] > 0) {
                $newRows = ReceiptRows::find($item['id']);
                $newRows->receipt_id = $receipt->id;
                $newRows->box_id = $item['Box']['id'];
                $newRows->discount_id = $item['Discount']['id'];
                $newRows->money = $item['money'];
                $newRows->discount = $item['discount'];
                $newRows->note = $item['note'] ?? null;
                $newRows->currency_id = $item['Currency']['id'];
                $newRows->exchange = $item['exchange'];
                $newRows->user_create_id = Auth::id();
                $newRows->created_at = now();
                $newRows->user_update_id = Auth::id();
                $newRows->updated_at = now();
                $newRows->save();
            } else {
                $newRows = new ReceiptRows();
                $newRows->receipt_id = $receipt->id;
                $newRows->box_id = $item['Box']['id'];
                $newRows->discount_id = $item['Discount']['id'];
                $newRows->money = $item['money'];
                $newRows->discount = $item['discount'];
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
            $receipt->ReceiptRows()->saveMany($arrayItemInsert);
        }
        // Process financial constraints for all rows
        $fc = new FinancialConstraintController();
        foreach ($arrayItemInsert as $row) {
            $DTOrow = new FinancialConstraintDTO(
                id: $row->id,
                model_name: ReceiptRows::class,
                currency_id: $row->currency_id,
                exchange: $row->exchange,
                date: $receipt->date,
                user_created_id: $row->user_create_id,
                user_input_id: $receipt->user_input_id,
                account_id: $receipt->account_id,
                debit: 0,
                credit: $row->money,
                note: $row->note
            );
            $fc->singleConstRow($DTOrow);
        }
        $receipt->update($validated);
        return $this->SuccessfullyResponse(
            new ReceiptResource($receipt->load(['UserInput', 'ReceiptRows' => fn($q) => $q->with($this->rowRelations)])),
            __('general.updateSuccess')
        );
    }

    public function destroy($id)
    {
        $receipt = Receipt::find($id);
        if (!$receipt) {
            return $this->FailedResponse(__('general.notFound'));
        }
        $fc = new FinancialConstraintController();
        foreach ($receipt->ReceiptRows as $row) {
            $fc->deleteFinancialConstraintByModel($row);
        }
        $receipt->ReceiptRows()->delete();
        $receipt->delete();
        return $this->SuccessfullyResponse(null, __('general.deleteSuccess'));
    }

    public function recover($id)
    {
        $receipt = Receipt::withTrashed()->find($id);
        $receipt->restore();
        $receipt->ReceiptRows()->withTrashed()->restore();

        $fc = new FinancialConstraintController();
        foreach ($receipt->ReceiptRows as $row) {
            $DTOrow = new FinancialConstraintDTO(
                id: $row->id,
                model_name: ReceiptRows::class,
                currency_id: $row->currency_id,
                exchange: $row->exchange,
                date: $receipt->date,
                user_created_id: $row->user_create_id,
                user_input_id: $receipt->user_input_id,
                account_id: $receipt->account_id,
                debit: $row->money,
                credit: 0,
                note: $row->note
            );
            $fc->singleConstRow($DTOrow);
        }

        return $this->SuccessfullyResponse(
            new ReceiptResource($receipt->load(['UserInput', 'ReceiptRows' => fn($q) => $q->with($this->rowRelations)])),
            __('general.recoverSuccess')
        );
    }
}
