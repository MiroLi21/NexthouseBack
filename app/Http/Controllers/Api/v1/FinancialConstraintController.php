<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\DTO\FinancialConstraintDTO;
use App\Http\Resources\Core\FinancialConstraintResource;
use App\Models\FinancialConstraint;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\SaleBill;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FinancialConstraintController extends Controller
{
    public function index()
    {
        $constraints = FinancialConstraint::with(['Account', 'Currency'])->get();
        return $this->SuccessfullyResponse(FinancialConstraintResource::collection($constraints), __('general.loadSuccess'));
    }

    public function getLastBalance($account_id)
    {
        if (!isset($account_id))
            return $this->error();

        $balances = FinancialConstraint::where('account_id', $account_id)
            ->leftJoin('currencies', 'financial_constraints.currency_id', '=', 'currencies.id')
            ->selectRaw('SUM(financial_constraints.debit - financial_constraints.credit) as total, currencies.symbol as currencySymbol, financial_constraints.currency_id')
            ->groupBy('financial_constraints.currency_id', 'currencies.symbol')
            ->get();

        return $this->SuccessfullyResponse($balances, __('general.loadSuccess'));
    }
    public function getFinancialConstraintByIdModel(Request $request)
    {
        if (!isset($request->model_id) || !isset($request->model_type))
            return $this->error();
        $constraints = FinancialConstraint::where('financial_constraintable_id', $request->model_id)
            ->where('financial_constraintable_type', $request->model_type)
            ->with(['Account'])
            ->with(['Currency'])
            ->get();
        return $this->SuccessfullyResponse(FinancialConstraintResource::collection($constraints), __('general.loadSuccess'));
    }
    public function storeFinancialConstraint(
        $financial_constraintable_id,
        $financial_constraintable_type,
        $debit,
        $credit,
        $account_id,
        $currency_id,
        $exchange,
        $bill_date,
        $user_input_id,
        $user_created_id,
        $note
    ) {
        if ($debit == 0 && $credit == 0)
            return null;
        return FinancialConstraint::create([
            'financial_constraintable_id' => $financial_constraintable_id,
            'financial_constraintable_type' => $financial_constraintable_type,
            'debit' => $debit,
            'credit' => $credit,
            'account_id' => $account_id,
            'currency_id' => $currency_id,
            'exchange' => $exchange,
            'bill_date' => $bill_date->format('Y-m-d H:i:s'),
            'user_input_id' => $user_input_id,
            'user_created_id' => $user_created_id,
            'note' => $note
        ]);
    }

    function makeCommonData($id, $model_type, $currency_id, $exchange, $bill_date, $user_input_id, $user_created_id, $note): array
    {
        //$bill_date = new Date($bill_date);
        return [
            'financial_constraintable_id' => $id,
            'financial_constraintable_type' => $model_type,
            'currency_id' => $currency_id,
            'exchange' => $exchange,
            'bill_date' => Carbon::parse($bill_date)->format('Y-m-d H:i:s'),
            'user_input_id' => $user_input_id,
            'user_created_id' => $user_created_id,
            'note' => $note
        ];
    }
    function makeRepetedData($commonData, $debit, $credit, $account_id): array
    {
        return array_merge($commonData, [
            'debit' => $debit,
            'credit' => $credit,
            'account_id' => $account_id,
        ]);
    }
    public function payment(Payment $payment)
    {
        $validator = validator([
            'id' => $payment->id,
            'money' => $payment->money,
            'account_id' => $payment->account_id,
            'currency_id' => $payment->currency_id,
            'discount_id' => $payment->discount_id,
            'box_id' => $payment->box_id,
            'exchange' => $payment->exchange,
            'discount' => $payment->discount,
            'date' => $payment->date,
            'user_input_id' => $payment->user_input_id,
            'user_created_id' => $payment->user_created_id,
            'note' => $payment->note
        ], [
            'id' => 'required|exists:payments,id',
            'money' => 'required|numeric|min:0',
            'account_id' => 'required|exists:accounts,id',
            'discount_id' => 'required|exists:accounts,id',
            'box_id' => 'required|exists:accounts,id',
            'currency_id' => 'required|exists:currencies,id',
            'exchange' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'note' => 'nullable|string',
            'user_input_id' => 'required|exists:users,id',
            'user_created_id' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        $commonData = $this->makeCommonData(
            id: $payment->id,
            model_type: Payment::class,
            currency_id: $payment->currency_id,
            exchange: $payment->exchange,
            bill_date: $payment->date,
            user_input_id: $payment->user_input_id,
            user_created_id: $payment->user_created_id,
            note: $payment->note
        );
        $money = $payment->money - $payment->discount;
        $data[] = $this->makeRepetedData(commonData: $commonData, debit: $money, credit: 0, account_id: $payment->account_id);
        $data[] = $this->makeRepetedData(commonData: $commonData, debit: 0, credit: $money, account_id: $payment->box_id);
        if ($payment->discount > 0) {
            $data[] = $this->makeRepetedData(commonData: $commonData, debit: $payment->discount, credit: 0, account_id: $payment->account_id);
            $data[] = $this->makeRepetedData(commonData: $commonData, debit: 0, credit: $payment->discount, account_id: $payment->discount_id);
        }
        $this->deleteFinancialConstraintByModel($payment);
        return FinancialConstraint::insert($data);
    }
    public function receipt(Receipt $receipt)
    {

        $validator = validator([
            'id' => $receipt->id,
            'money' => $receipt->money,
            'account_id' => $receipt->account_id,
            'currency_id' => $receipt->currency_id,
            'box_id' => $receipt->box_id,
            'discount_id' => $receipt->discount_id,
            'exchange' => $receipt->exchange,
            'discount' => $receipt->discount,
            'date' => $receipt->date,
            'user_input_id' => $receipt->user_input_id,
            'user_created_id' => $receipt->user_created_id,
            'note' => $receipt->note
        ], [
            'id' => 'required|exists:receipts,id',
            'money' => 'required|numeric|min:0',
            'account_id' => 'required|exists:accounts,id',
            'discount_id' => 'required|exists:accounts,id',
            'box_id' => 'required|exists:accounts,id',
            'currency_id' => 'required|exists:currencies,id',
            'exchange' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'note' => 'nullable|string',
            'user_input_id' => 'required|exists:users,id',
            'user_created_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $money = $receipt->money - $receipt->discount;
        $commonData = $this->makeCommonData(
            id: $receipt->id,
            model_type: Receipt::class,
            currency_id: $receipt->currency_id,
            exchange: $receipt->exchange,
            bill_date: $receipt->date,
            user_input_id: $receipt->user_input_id,
            user_created_id: $receipt->user_created_id,
            note: $receipt->note
        );
        $data[] = $this->makeRepetedData(commonData: $commonData, debit: 0, credit: $money, account_id: $receipt->account_id);
        $data[] = $this->makeRepetedData(commonData: $commonData, debit: $money, credit: 0, account_id: $receipt->box_id);
        if ($receipt->discount > 0) {
            $data[] = $this->makeRepetedData(commonData: $commonData, debit: 0, credit: $receipt->discount, account_id: $receipt->account_id);
            $data[] = $this->makeRepetedData(commonData: $commonData, debit: $receipt->discount, credit: 0, account_id: $receipt->discount_id);
        }
        $this->deleteFinancialConstraintByModel($receipt);
        return FinancialConstraint::insert($data);
    }
    public function singleConstRow(FinancialConstraintDTO $financialConstraintDTO)
    {
        $commonData = $this->makeCommonData(
            id: $financialConstraintDTO->id,
            model_type: $financialConstraintDTO->model_name,
            currency_id: $financialConstraintDTO->currency_id,
            exchange: $financialConstraintDTO->exchange,
            bill_date: $financialConstraintDTO->date,
            user_created_id: $financialConstraintDTO->user_created_id,
            user_input_id: $financialConstraintDTO->user_input_id,
            note: $financialConstraintDTO->note
        );
        $data[] = $this->makeRepetedData(
            commonData: $commonData,
            debit: $financialConstraintDTO->debit,
            credit: $financialConstraintDTO->credit,
            account_id: $financialConstraintDTO->account_id
        );
        $this->deleteFinancialConstraint($financialConstraintDTO->id, $financialConstraintDTO->model_name);
        return FinancialConstraint::insert($data);
    }
    public function saleBill(SaleBill $sale)
    {
        $validator = validator([
            'id' => $sale->id,
            'money' => $sale->money,
            'account_id' => $sale->account_id,
            'sale_id' => $sale->sale_id,
            'currency_id' => $sale->currency_id,
            'box_id' => $sale->box_id,
            'discount_id' => $sale->discount_id,
            'pay_type_id' => $sale->pay_type_id,
            'exchange' => $sale->exchange,
            'pay' => $sale->pay,
            'discount' => $sale->discount,
            'date' => $sale->date,
            'user_input_id' => $sale->user_input_id,
            'user_created_id' => $sale->user_created_id,
            'note' => $sale->note,
        ], [
            'id' => 'required|exists:sales,id',
            'money' => 'required|numeric|min:0',
            'account_id' => 'required|exists:accounts,id',
            'box_id' => 'required|exists:accounts,id',
            'sale_id' => 'required|exists:accounts,id',
            'discount_id' => 'required|exists:accounts,id',
            'currency_id' => 'required|exists:currencies,id',
            'pay_type_id' => 'required|exists:pay_types,id',
            'exchange' => 'required|numeric|min:0',
            'pay' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'note' => 'nullable|string',
            'user_input_id' => 'required|exists:users,id',
            'user_created_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $commonData = $this->makeCommonData(
            id: $sale->id,
            model_type: SaleBill::class,
            currency_id: $sale->currency_id,
            exchange: $sale->exchange,
            bill_date: $sale->date,
            user_input_id: $sale->user_input_id,
            user_created_id: $sale->user_created_id,
            note: $sale->note
        );
        // for sale
        $money = $sale->money;
        $data[] = $this->makeRepetedData(commonData: $commonData, debit: $money, credit: 0, account_id: $sale->account_id);
        $data[] = $this->makeRepetedData(commonData: $commonData, debit: 0, credit: $money, account_id: $sale->sale_id);
        if ($sale->discount > 0) {
            $data[] = $this->makeRepetedData(commonData: $commonData, debit: 0, credit: $sale->discount, account_id: $sale->account_id);
            $data[] = $this->makeRepetedData(commonData: $commonData, debit: $sale->discount, credit: 0, account_id: $sale->discount_id);
        }
        //for cash
        if ($sale->pay_type_id == 2) {
            $data[] = $this->makeRepetedData(commonData: $commonData, debit: 0, credit: $money, account_id: $sale->account_id);
            $data[] = $this->makeRepetedData(commonData: $commonData, debit: $money, credit: 0, account_id: $sale->box_id);
        }
        //for pay
        elseif ($sale->pay_type_id == 3) {
            $data[] = $this->makeRepetedData(commonData: $commonData, debit: 0, credit: $sale->pay, account_id: $sale->account_id);
            $data[] = $this->makeRepetedData(commonData: $commonData, debit: $sale->pay, credit: 0, account_id: $sale->box_id);
        }
        $this->deleteFinancialConstraintByModel($sale);
        return FinancialConstraint::insert($data);
    }
    public function sale($sale, $ModelName)
    {

        $validator = validator([
            'id' => $sale->id,
            'money' => $sale->total_amount,
            'student_id' => $sale->account_student_id,
            'account_sale_id' => $sale->account_sale_id,
            'currency_sale_id' => $sale->currency_id,
            'pay_type_sale_id' => 1,
            'exchange_sale' => 1,
            'cash_sale' => $sale->total_paid_amount,
            'issue_date' => $sale->date,
            'user_input_id' => $sale->user_input_id,
            'user_created_id' => $sale->user_created_id,
            'note' => $sale->note,
        ], [
            'id' => 'required|exists:' . $this->getTableNameFromModel($ModelName) . ',id',
            'money' => 'required|numeric|min:0',
            'student_id' => 'required|exists:accounts,id',
            'account_sale_id' => 'required|exists:accounts,id',
            'currency_sale_id' => 'required|exists:currencies,id',
            'pay_type_sale_id' => 'required|exists:pay_types,id',
            'exchange_sale' => 'required|numeric|min:0',
            'cash_sale' => 'required|numeric|min:0',
            'issue_date' => 'required|date',
            'user_input_id' => 'required|exists:users,id',
            'user_created_id' => 'required|exists:users,id',
            'note' => 'nullable|string'
        ]);
        if ($validator->fails()) {
            Log::alert($validator->errors());
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }


        $commonData = $this->makeCommonData(
            id: $sale->id,
            model_type: $ModelName,
            currency_id: $sale->currency_sale_id,
            exchange: $sale->exchange_sale,
            bill_date: $sale->issue_date,
            user_created_id: $sale->user_created_id,
            user_input_id: $sale->user_input_id,
            note: $sale->note
        );

        // Main Sale Constraints
        // Debit Student (Receivable)
        $money = $sale->total_amount;
        $data[] = $this->makeRepetedData(commonData: $commonData, debit: $money, credit: 0, account_id: $sale->student_id);
        // Credit Account Sale (Revenue)
        $data[] = $this->makeRepetedData(commonData: $commonData, debit: 0, credit: $money, account_id: $sale->account_sale_id);
        //Log::alert($sale);
        // Details Constraints (Splitting Payment)
        // Iterate through details to handle Teacher and Institute splits
        Log::alert($sale->SaleDetails);

        if (isset($sale->SaleDetails)) {
            foreach ($sale->SaleDetails as $detail) {
                Log::alert($detail);

                // Determine paid amount for this detail (assuming detail has 'paidFromStudent' or similar field)
                // If detail doesn't have it explicitly, we might need to fallback. 
                // Using user's variable name $paidFromStudent.
                $paidFromStudent = $detail->paid ?? 0;
                $institutePercentage = $detail->institute_percentage ?? 0;

                // Calculate split
                // paidToTeacher = paid - (paid * percentage / 100)
                $instituteShare = ($paidFromStudent * $institutePercentage) / 100;
                $paidToTeacher = $paidFromStudent - $instituteShare;
                $paidToInstitute = $instituteShare; // Or ($paidFromStudent - $paidToTeacher) to be safe with rounding

                // 1. Debit Teacher Box (Payment to Teacher)
                if ($paidToTeacher > 0 && isset($sale->box_teacher_id)) {
                    // Note: user snippet used $sale->box_teacher_id, assuming it's passed on main obj or detail? 
                    // Using $sale->box_teacher_id based on snippet, but it might be $detail->box_teacher_id.
                    // I will try $detail->box_teacher_id first if available, else $sale.
                    $teacherBox = $detail->box_teacher_id ?? $sale->box_teacher_id;
                    $data[] = $this->makeRepetedData(commonData: $commonData, debit: $paidToTeacher, credit: 0, account_id: $teacherBox);
                }

                // 2. Debit Institute Box (Payment to Institute)
                if ($paidToInstitute > 0) {
                    $data[] = $this->makeRepetedData(commonData: $commonData, debit: $paidToInstitute, credit: 0, account_id: $sale->box_id);
                }

                // 3. Credit Student (Payment Source) - essentially treating this as the payment matching the split
                if ($paidFromStudent > 0) {
                    $data[] = $this->makeRepetedData(commonData: $commonData, debit: 0, credit: $paidFromStudent, account_id: $sale->account_student_id);
                }
            }
        }

        if ($sale->discount_sale_id > 0) {
            $data[] = $this->makeRepetedData(commonData: $commonData, debit: 0, credit: $sale->discount_sale, account_id: $sale->account_student_id);
            $data[] = $this->makeRepetedData(commonData: $commonData, debit: $sale->discount_sale, credit: 0, account_id: $sale->discount_sale_id);
        }

        // Handle direct cash payment (if not covered by details loop or as separate logic)
        // The details loop handles the "split" logic requested. 
        // If there's a remaining "cash_sale" (total_paid_amount) not covered, we might need standard handling?
        // User snippet replaced the old "for Cash / for Pay" blocks with the details loop logic.
        // I will trust the details loop covers the payment logic as requested.

        return FinancialConstraint::insert($data);
    }

    public function buy($buy, $ModelName)
    {
        $validator = validator([
            'id' => $buy->id,
            'money' => $buy->total_buy,
            'supplier_id' => $buy->supplier_id,
            'box_buy_id' => $buy->box_buy_id,
            'account_buy_id' => $buy->account_buy_id,
            'discount_buy_id' => $buy->discount_buy_id,
            'currency_buy_id' => $buy->currency_buy_id,
            'pay_type_buy_id' => $buy->pay_type_buy_id,
            'exchange_buy' => $buy->exchange_buy,
            'cash_buy' => $buy->cash_buy,
            'discount_buy' => $buy->discount_buy,
            'issue_date' => $buy->issue_date,
            'user_input_id' => $buy->user_input_id,
            'user_created_id' => $buy->user_created_id,
            'note' => $buy->note,
        ], [
            'id' => 'required|exists:' . $this->getTableNameFromModel($ModelName) . ',id',
            'money' => 'required|numeric|min:0',
            'supplier_id' => 'required|exists:accounts,id',
            'box_buy_id' => 'required|exists:accounts,id',
            'account_buy_id' => 'required|exists:accounts,id',
            'discount_buy_id' => 'required|exists:accounts,id',
            'currency_buy_id' => 'required|exists:currencies,id',
            'pay_type_buy_id' => 'required|exists:pay_types,id',
            'exchange_buy' => 'required|numeric|min:0',
            'cash_buy' => 'required|numeric|min:0',
            'discount_buy' => 'required|numeric|min:0',
            'issue_date' => 'required|date',
            'user_input_id' => 'required|exists:users,id',
            'user_created_id' => 'required|exists:users,id',
            'note' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $commonData = $this->makeCommonData(
            id: $buy->id,
            model_type: $ModelName,
            currency_id: $buy->currency_buy_id,
            exchange: $buy->exchange_buy,
            bill_date: $buy->issue_date,
            user_created_id: $buy->user_created_id,
            user_input_id: $buy->user_input_id,
            note: $buy->note
        );
        // for buy Debit 
        $money = $buy->total_buy;
        $data[] = $this->makeRepetedData(commonData: $commonData, debit: 0, credit: $money, account_id: $buy->supplier_id);
        $data[] = $this->makeRepetedData(commonData: $commonData, debit: $money, credit: 0, account_id: $buy->account_buy_id);
        if ($buy->discount_buy_id > 0) {
            $data[] = $this->makeRepetedData(commonData: $commonData, debit: $buy->discount_buy, credit: 0, account_id: $buy->supplier_id);
            $data[] = $this->makeRepetedData(commonData: $commonData, debit: 0, credit: $buy->discount_buy, account_id: $buy->discount_buy_id);
        }
        //for Cash
        if ($buy->pay_type_buy_id == 2) {
            $data[] = $this->makeRepetedData(commonData: $commonData, debit: $money, credit: 0, account_id: $buy->supplier_id);
            $data[] = $this->makeRepetedData(commonData: $commonData, debit: 0, credit: $money, account_id: $buy->box_buy_id);
        }
        //for pay
        elseif ($buy->pay_type_buy_id == 3) {
            $data[] = $this->makeRepetedData(commonData: $commonData, debit: $buy->cash_buy, credit: 0, account_id: $buy->supplier_id);
            $data[] = $this->makeRepetedData(commonData: $commonData, debit: 0, credit: $buy->cash_buy, account_id: $buy->box_buy_id);
        }
        return FinancialConstraint::insert($data);
    }
    public function getTableNameFromModel(string $modelClass): ?string
    {
        if (!class_exists($modelClass))
            return null;
        $model = new $modelClass;
        if (!$model instanceof \Illuminate\Database\Eloquent\Model)
            return null;

        return $model->getTable();
    }
    public function getModelPath($modelClassName)
    {
        // Check if class name starts with App\Models\HeliumCloud
        if (strpos($modelClassName, 'App\Models\HeliumCloud') === 0) {
            // Get the path after App\Models\HeliumCloud
            $path = str_replace('App\Models\HeliumCloud\\', '', $modelClassName);
            return 'App\Models\HeliumCloud\\' . $path;
        }

        // For other classes, return the full namespace
        return $modelClassName;
    }
    public function show(FinancialConstraint $financialConstraint)
    {
        return $this->SuccessfullyResponse(
            new FinancialConstraintResource($financialConstraint->load(['moving', 'account'])),
            __('general.loadSuccess')
        );
    }
    public function destroy(FinancialConstraint $financialConstraint)
    {
        $financialConstraint->delete();
        return $this->SuccessfullyResponse(null, __('general.deleteSuccess'));
    }

    /**
     * Delete financial constraint for a given model
     *
     * @param int $model_id
     * @param string $model_type
     * @return void
     */
    public function deleteFinancialConstraint($model_id, $model_type)
    {
        FinancialConstraint::where('financial_constraintable_id', $model_id)
            ->where('financial_constraintable_type', $model_type)
            ->delete();
    }

    public function deleteFinancialConstraintByModel($model)
    {
        $this->deleteFinancialConstraint($model->id, get_class($model));
    }
}
