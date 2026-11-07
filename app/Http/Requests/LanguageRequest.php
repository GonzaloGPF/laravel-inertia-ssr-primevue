<?php

namespace App\Http\Requests;

use App\Enums\Languages;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class LanguageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request. It is for everybody, the ones that are not logged too.
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
            'language' => ['required', new Enum(Languages::class)],
        ];
    }
}
