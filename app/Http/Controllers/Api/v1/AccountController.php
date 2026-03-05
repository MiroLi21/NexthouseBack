<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\EnumAccountType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Core\AccountLiteResource;
use App\Http\Resources\Core\AccountResource;
use App\Http\Resources\LiteResource;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::with(['AccountType', 'Parent', 'AccountCard', 'AgentCard', 'SupplierCard'])->get();
        return $this->SuccessfullyResponse(AccountResource::collection($accounts), __('general.loadSuccess'));
    }
    public function get_allAccountsLite()
    {
        $accounts = Account::with(['AccountType', 'Parent'])->get();
        return $this->SuccessfullyResponse(LiteResource::collection($accounts), __('general.loadSuccess'));
    }
    public function get_baseCards()
    {
        $accounts = Account::where('account_type_id', EnumAccountType::BaseCard->value)->with(['AccountType', 'Parent'])->get();
        return $this->SuccessfullyResponse(AccountResource::collection($accounts), __('general.loadSuccess'));
    }
    public function get_baseCardsMini()
    {
        $accounts = Account::where('account_type_id', EnumAccountType::BaseCard->value)->get();
        return $this->SuccessfullyResponse(AccountLiteResource::collection($accounts), __('general.loadSuccess'));
    }
    public function get_cards()
    {
        $accounts = Account::where('account_type_id', EnumAccountType::Card->value)->with(['AccountType', 'Parent'])->get();
        return $this->SuccessfullyResponse(AccountResource::collection($accounts), __('general.loadSuccess'));
    }
    public function get_boxes()
    {
        $accounts = Account::where('account_type_id', EnumAccountType::Box->value)->with(['AccountType', 'Parent'])->get();
        return $this->SuccessfullyResponse(AccountResource::collection($accounts), __('general.loadSuccess'));
    }
    public function get_salaries()
    {
        $accounts = Account::where('account_type_id', EnumAccountType::Salary->value)->with(['AccountType', 'Parent'])->get();
        return $this->SuccessfullyResponse(AccountResource::collection($accounts), __('general.loadSuccess'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:accounts,id',
            'account_type_id' => 'required|exists:account_types,id',
        ]);
        $account = Account::create($validated);
        return $this->SuccessfullyResponse(new AccountResource($account), __('general.createSuccess'));
    }

    public function show(Account $account)
    {
        $account->load('accountType');
        return $this->SuccessfullyResponse(new AccountResource($account), __('general.loadSuccess'));
    }

    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'account_type_id' => 'required|exists:account_types,id',
        ]);

        $account->update($validated);
        return $this->SuccessfullyResponse(new AccountResource($account), __('general.updateSuccess'));
    }

    public function destroy(Account $account)
    {
        $account->delete();
        return $this->SuccessfullyResponse(null, __('general.deleteSuccess'));
    }
}
