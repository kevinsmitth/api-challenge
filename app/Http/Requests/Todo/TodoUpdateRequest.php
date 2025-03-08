<?php

namespace App\Http\Requests\Todo;

use App\Enums\TodoColorEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

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
            'description' => 'string|max:2000',
            'color' => ['string', new Enum(TodoColorEnum::class)],
            'completed' => 'boolean',
            'favorite' => 'boolean',
        ];
    }
}
