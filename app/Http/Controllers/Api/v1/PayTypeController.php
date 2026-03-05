<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Core\PayTypeResource;
use App\Models\PayType;
use Illuminate\Http\Request;

class PayTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $data = PayType::get();
        if (empty($data) || $data == null) {
            return $this->FailedResponse(__('general.loadFailed'), 204);
        } else {
            return $this->SuccessfullyResponse(PayTypeResource::collection($data), __('general.loadSuccess'));
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index_debit_cash_type()
    {

        $data = PayType::where('id', '!=', 3)->get();
        if (empty($data) || $data == null) {
            return $this->FailedResponse(__('general.loadFailed'), 204);
        } else {
            return $this->SuccessfullyResponse(PayTypeResource::collection($data), __('general.loadSuccess'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
