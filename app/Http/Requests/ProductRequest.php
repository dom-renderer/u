<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if (request()->method() == 'PUT') {

            $id = decrypt($this->pid);

            return ['sku' => ['required', function ($name, $value, $fail) use ($id) {
                if (\App\Models\Product::where('sku', $value)->where('id', '!=', $id)->withTrashed()->exists()) {
                    $fail("Product with this SKU already exists.");
                }
            }], 
            'name' => 'required',
            'category' => 'required',
            'unit' => 'required',
            'description' => 'required'
        ];
        } else {
            return ['sku' => ['required', function ($name, $value, $fail) {
                if (\App\Models\Product::where('sku', $value)->withTrashed()->exists()) {
                    $fail("Product with this SKU already exists.");
                }
            }],
            'name' => 'required',
            'category' => 'required',
            'unit' => 'required',
            'description' => 'required'
        ];
        }
    }

    public function messages(): array
    {
        return [
            'sku.required' => 'SKU is required.',
            'name.required' => 'Name is required.',
            'category.required' => 'Category is required.',
            'unit.required' => 'Select a unit.',
            'description.required' => 'Description is required.'
        ];
    }
}
