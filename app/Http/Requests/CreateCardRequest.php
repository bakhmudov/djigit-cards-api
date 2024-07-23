<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Разрешить всем авторизованным пользователям
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fio' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'job_position' => 'nullable|string|max:255',
        ];
    }


    public function messages()
    {
        return [
            'fio.required' => 'Поле "fio" обязательно для заполнения.'
        ];
    }
}
