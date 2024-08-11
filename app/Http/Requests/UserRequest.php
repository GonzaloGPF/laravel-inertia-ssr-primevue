<?php

namespace App\Http\Requests;

use App\Enums\Currencies;
use App\Enums\Languages;
use App\Enums\Roles;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Symfony\Component\HttpFoundation\Request;

class UserRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        if ($this->isMethod(Request::METHOD_POST)) {
            return [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users',
                'role' => ['required', new Enum(Roles::class)],
                'phone' => 'nullable|string|max:255',
                'language' => ['nullable', new Enum(Languages::class)],
                'currency' => ['nullable', new Enum(Currencies::class)],
            ];
        } else {
            return [
                'name' => 'string|max:255',
                'email' => ['email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user')->id)],
                'role' => [new Enum(Roles::class)],
                'phone' => 'nullable|string|max:255',
                'language' => [new Enum(Languages::class)],
                'currency' => [new Enum(Currencies::class)],
            ];
        }
    }
}
