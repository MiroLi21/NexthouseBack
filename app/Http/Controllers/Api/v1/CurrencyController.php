<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Core\CurrencyRequest;
use App\Http\Resources\Core\CurrencyLiteResource;
use App\Http\Resources\Core\CurrencyResource;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $data = Currency::get();
        if ($data->isEmpty()) {
            return $this->FailedResponse(__('general.loadFailed'), 204);
        } else {
            return $this->SuccessfullyResponse(CurrencyResource::collection($data->fresh()), __('general.loadSuccess'));
        }
    }
    public function getLite()
    {
        $data = Currency::get();
        if ($data->isEmpty()) {
            return $this->FailedResponse(__('general.loadFailed'), 204);
        } else {
            return $this->SuccessfullyResponse(CurrencyLiteResource::collection($data->fresh()), __('general.loadSuccess'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CurrencyRequest $request)
    {

        $validatedData = $request->validated();
        $validatedData['user_id'] = Auth::id();
        $validatedData['exchange'] = $request->exchange;// * 100;

        $data = Currency::Create($validatedData);

        if (empty($data) || $data == null) {
            return $this->FailedResponse(__('general.saveUnsuccessfully'));
        } else {
            return $this->SuccessfullyResponse(new CurrencyResource($data->fresh()), __('general.saveSuccessfully'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $currency_id)
    {

        $data = Currency::find($currency_id);
        if (empty($data) || $data == null) {
            return $this->FailedResponse(__('general.loadFailed'), 204);
        } else {
            return $this->SuccessfullyResponse(new CurrencyResource($data), __('general.loadSuccess'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(CurrencyRequest $request, $id)
    {
        $data = Currency::find($id);

        if (!$data) {
            return $this->FailedResponse(__('general.loadFailed'), 204);
        }

        $validatedData = $request->validated();
        if ($request->has('exchange')) {
            $validatedData['exchange'] = $request->exchange;// * 100;
        }

        if (!$data->update($validatedData)) {
            return $this->FailedResponse(__('general.saveUnsuccessfully'));
        } else {
            return $this->SuccessfullyResponse(new CurrencyResource($data->fresh()), __('general.saveSuccessfully'));
        }
    }
    /**
     * Update the order of resources in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrder(Request $request)
    {
        Log::alert($request->all());
        $validated = $request->validate([
            'currencies' => 'required|array',
            'currencies.*.id' => 'required|integer|exists:currencies,id',
            'currencies.*.order' => 'required|integer',
        ]);

        foreach ($validated['currencies'] as $currencyData) {
            Currency::where('id', $currencyData['id'])->update(['order' => $currencyData['order']]);
        }

        return $this->SuccessfullyResponse(null, __('general.saveSuccessfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {

        $data = Currency::find($id);

        if (!$data) {
            return $this->FailedResponse(__('general.deleteUnsuccessfully'));
        }

        if (!$data->delete()) {
            return $this->FailedResponse(__('general.deleteUnsuccessfully'));
        } else {
            return $this->SuccessfullyResponse(new CurrencyResource($data), __('general.deleteSuccessfully'));
        }
    }
}
