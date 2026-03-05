<?php

namespace App\Http\Requests\api;

use Illuminate\Foundation\Http\FormRequest;

class StoreSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'key' => 'required|string|unique:settings,key',
            'value1' => 'nullable|string',
            'value2' => 'nullable|string',
            'value3' => 'nullable|integer',
            'value4' => 'nullable|dateTime|date',
            'value5' => 'nullable|boolean',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean'
        ];
    }
}