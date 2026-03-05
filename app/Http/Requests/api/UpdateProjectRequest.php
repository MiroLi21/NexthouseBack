<?php

namespace App\Http\Requests\api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'description' => 'nullable|string|max:2000',
            'category_id' => 'sometimes|required|integer|exists:categories,id',
        ];
    }
}
