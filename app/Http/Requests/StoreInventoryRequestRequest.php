<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryRequestRequest extends FormRequest
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
            'foreman_name' => [
                'required',
                'string',
                'max:150',
            ],

            'project_name' => [
                'required',
                'string',
                'max:200',
            ],

            'work_area' => [
                'required',
                'string',
                'max:200',
            ],

            'requested_at' => [
                'required',
                'date',
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
            'foreman_name' => trim(
                (string) $this->input('foreman_name')
            ),

            'project_name' => trim(
                (string) $this->input('project_name')
            ),

            'work_area' => trim(
                (string) $this->input('work_area')
            ),
        ]);
    }
}