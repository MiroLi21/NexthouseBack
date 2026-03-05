<?php

namespace App\Http\DTO;

class FinancialConstraintDTO
{
    public int $id;
    public string $model_name;
    public int $currency_id;
    public float $exchange;
    public string $date;
    public int $user_created_id;
    public int $user_input_id;
    public ?string $note;
    public int $account_id;
    public float $debit;
    public float $credit;
    public function __construct(
        int $id,
        string $model_name,
        int $currency_id,
        float $exchange,
        string $date,
        int $user_created_id,
        int $user_input_id,
        int $account_id,
        float $debit,
        float $credit,
        ?string $note = null
    ) {
        $this->id = $id;
        $this->model_name = $model_name;
        $this->currency_id = $currency_id;
        $this->exchange = $exchange;
        $this->date = $date;
        $this->user_created_id = $user_created_id;
        $this->user_input_id = $user_input_id;
        $this->note = $note;
        $this->account_id = $account_id;
        $this->debit = $debit;
        $this->credit = $credit;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            model_name: $data['model_name'],
            currency_id: $data['currency_id'],
            exchange: $data['exchange'],
            date: $data['date'],
            user_created_id: $data['user_created_id'],
            user_input_id: $data['user_input_id'],
            note: $data['note'] ?? null,
            account_id: $data['account_id'],
            debit: $data['debit'],
            credit: $data['credit']
        );
    }
}
