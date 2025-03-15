<?php

namespace App\Http\Requests\Todo;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TodoUpdateRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'string|max:255',
            'description' => 'string|max:255',
            'color' => 'string|regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/',
            'completed' => 'boolean',
            'favorite' => 'boolean',
        ];
    }
}
