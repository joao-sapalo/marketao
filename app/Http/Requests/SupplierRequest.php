<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required'],
            'phone' => ['nullable'],
            'email' => ['nullable', 'email'],
            'nif' => ['nullable'],
            'address' => ['nullable'],
            'city' => ['nullable'],
            'notes' => ['nullable'],
        ];
    }
}
