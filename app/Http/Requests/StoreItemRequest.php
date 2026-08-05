<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreItemRequest extends FormRequest
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
            'item_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('items', 'item_code'),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'category' => [
                'required',
                Rule::in([
                    'tool',
                    'equipment',
                    'material',
                    'consumable',
                ]),
            ],
            'consumption_type' => [
                'required',
                Rule::in([
                    'consumable',
                    'non-consumable',
                ]),
            ],
            'unit' => [
                'required',
                'string',
                'max:50',
            ],
            'initial_stock' => [
                'required',
                'integer',
                'min:0',
            ],
            'minimum_stock_level' => [
                'required',
                'integer',
                'min:0',
            ],
            'storage_location' => [
                'nullable',
                'string',
                'max:150',
            ],
            'remarks' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'item_code' => strtoupper(
                trim((string) $this->input('item_code'))
            ),
            'name' => trim((string) $this->input('name')),
        ]);
    }
}