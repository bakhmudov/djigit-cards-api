<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCardRequest extends FormRequest
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
            'fio' => 'sometimes|required|string|max:255',
            'about_me' => 'nullable|string',
            'company_name' => 'nullable|string|max:255',
            'job_position' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'remove_photo' => 'nullable|boolean',
            'main_info' => 'nullable|array',
            'main_info.phone' => 'nullable|string|max:25',
            'main_info.telegram' => 'nullable|string|max:255',
            'main_info.whatsapp' => 'nullable|string|max:255',
            'main_info.instagram' => 'nullable|string|max:255',
            'phones' => 'nullable|array',
            'emails' => 'nullable|array',
            'addresses' => 'nullable|array',
            'websites' => 'nullable|array',
        ];
    }
}
