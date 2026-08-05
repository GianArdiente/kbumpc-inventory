<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdjustItemStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin'
            && $this->user()?->status === 'active';
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => [
                'required',
                Rule::in([
                    'stock_in',
                    'correction',
                ]),
            ],
            'overall_change' => [
                'required',
                'integer',
            ],
            'available_change' => [
                'required',
                'integer',
            ],
            'remarks' => [
                'required',
                'string',
                'max:1000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'remarks.required' =>
                'A reason is required for every stock adjustment.',
        ];
    }
}