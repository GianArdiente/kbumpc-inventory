<?php

namespace App\Http\Requests;

use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemRequest extends FormRequest
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
        /** @var Item $item */
        $item = $this->route('item');

        return [
            'item_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('items', 'item_code')
                    ->ignore($item->id),
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

            'unit_cost' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999.99',
                'decimal:0,2',
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

            'name' => trim(
                (string) $this->input('name')
            ),

            'unit' => trim(
                (string) $this->input('unit')
            ),

            'unit_cost' => $this->filled('unit_cost')
                ? $this->input('unit_cost')
                : null,
        ]);
    }
}