<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = null;
        $product = $this->route('product');
        if ($product instanceof \App\Models\Product) {
            $productId = $product->id;
        }

        $uniqueRule = 'unique:products,code';
        if ($productId) {
            $uniqueRule .= ',' . $productId;
        }

        return [
            'code' => ['required', $uniqueRule],
            'name' => ['required'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'image' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
