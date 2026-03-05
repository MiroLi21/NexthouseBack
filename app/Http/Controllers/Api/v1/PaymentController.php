<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\v1\FinancialConstraintController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PaymentRequest;
use App\Http\Resources\Voucher\PaymentResource;
use App\Models\Payment;
use App\Traits\HasNavigation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    use HasNavigation;

    protected $modelClass = Payment::class;
    protected $resourceClass = PaymentResource::class;
    protected $relations = [
        'Account',
        'Currency',
        'Box',
        'UserInput',
        'FinancialConstraints'
    ];
    public function index()
    {
        $payments = Payment::with([
            'Account',
            'Currency',
            'UserInput',
        ])->get();

        return $this->SuccessfullyResponse(
            PaymentResource::collection($payments),
            __('general.loadSuccess')
        );
    }
    public function getByAccount($id)
    {
        $payments = Payment::with([
            'Account',
            'Currency',
            'UserInput',
        ])->where('account_id', $id)->get();

        return $this->SuccessfullyResponse(
            PaymentResource::collection($payments),
            __('general.loadSuccess')
        );
    }

    public function store(PaymentRequest $request)
    {
        $validated = $request->validated();
        $validated += [
            'user_created_id' => Auth::id(),
            'user_updated_id' => Auth::id(),
            'discount_id' => 50
        ];
        $payment = Payment::create($validated);
        $fc = new FinancialConstraintController();
        $fc->payment($payment);

        return $this->SuccessfullyResponse(
            new PaymentResource($payment->load([
                'Account',
                'Currency',
                'Box',
                'UserInput',
                'FinancialConstraints'
            ])),
            __('general.createSuccess')
        );
    }

    public function update(PaymentRequest $request, $id)
    {
        $payment = Payment::find($id);
        if (!$payment) {
            return $this->FailedResponse(__('general.notFound'));
        }
        $validated = $request->validated();

        $payment->update($validated);
        $fc = new FinancialConstraintController();
        $fc->payment($payment);

        return $this->SuccessfullyResponse(
            new PaymentResource($payment->load([
                'Account',
                'Currency',
                'Box',
                'UserInput',
                'FinancialConstraints'
            ])),
            __('general.updateSuccess')
        );
    }

    public function show($id)
    {
        $payment = Payment::find($id);
        if (!$payment) {
            return $this->FailedResponse(__('general.notFound'));
        }
        return $this->SuccessfullyResponse(
            new PaymentResource($payment->load([
                'Account',
                'Currency',
                'Box',
                'UserInput',
                'FinancialConstraints'
            ])),
            __('general.loadSuccess')
        );
    }
    public function destroy($id)
    {
        $payment = Payment::find($id);
        if (!$payment) {
            return $this->FailedResponse(__('general.notFound'));
        }
        $fc = new FinancialConstraintController();
        $fc->deleteFinancialConstraintByModel($payment);
        $payment->delete();
        return $this->SuccessfullyResponse(null, __('general.deleteSuccess'));
    }
    public function recover($id)
    {
        $payment = Payment::withTrashed()->find($id);
        if (!$payment) {
            return $this->FailedResponse(__('general.notFound'));
        }
        $payment->restore();


        $fc = new FinancialConstraintController();
        $fc->payment($payment);
        return $this->SuccessfullyResponse(
            new PaymentResource($payment->load([
                'Account',
                'Currency',
                'Box',
                'UserInput',
                'FinancialConstraints'
            ])),
            __('general.recoverSuccess')
        );
    }
}
