<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequestItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->status === 'active'
            && in_array(
                $this->user()?->role,
                ['admin', 'staff'],
                true
            );
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'item_id' => [
                'required',
                'integer',
                'exists:items,id',
            ],

            'requested_quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'item_id.required' =>
                'Please select an inventory item.',

            'item_id.exists' =>
                'The selected inventory item no longer exists.',

            'requested_quantity.min' =>
                'Requested quantity must be at least one.',
        ];
    }
}