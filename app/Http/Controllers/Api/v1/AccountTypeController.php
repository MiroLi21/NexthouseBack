<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Core\AccountTypeResource;
use App\Models\Core\AccountType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = AccountType::get();
        if (empty($data) || $data == null) {
            return $this->FailedResponse(__('general.loadFailed'), 204);
        } else {
            return $this->SuccessfullyResponse(AccountTypeResource::collection($data->fresh()), __('general.loadSuccess'));
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
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(int $account_type_id)
    {

        $data = AccountType::find($account_type_id);
        if (empty($data) || $data == null) {
            return $this->FailedResponse(__('general.loadFailed'), 204);
        } else {
            return $this->SuccessfullyResponse(new AccountTypeResource($data), __('general.loadSuccess'));
        }
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
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $data = AccountType::find($id);
        $data->delete();
        if (empty($data) || $data == null) {

            return $this->FailedResponse(__('general.deleteUnsuccessfully'));
        } else {
            return $this->SuccessfullyResponse(new AccountTypeResource($data), __('general.deleteSuccessfully'));
        }
    }
}
