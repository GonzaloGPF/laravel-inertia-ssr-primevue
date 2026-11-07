<?php

namespace App\Http\Requests;

use App\Enums\Currencies;
use App\Enums\Languages;
use App\Enums\Roles;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Symfony\Component\HttpFoundation\Request;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request. It is done here, not in the controller, as
     * the validation comes first: the ones that cannot would otherwise learn from its errors (i.e. which emails exist).
     */
    public function authorize(): bool
    {
        if ($this->isMethod(Request::METHOD_POST)) {
            return Gate::allows('create', User::class);
        }

        return Gate::allows('update', $this->route('user'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        if ($this->isMethod(Request::METHOD_POST)) {
            return [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users',
                'role' => ['required', new Enum(Roles::class)],
                'phone' => 'nullable|string|max:255',
                'age' => 'nullable|integer|min:0|max:150',
                'language' => ['nullable', new Enum(Languages::class)],
                'currency' => ['nullable', new Enum(Currencies::class)],
                'avatar' => User::fileRules('avatar'),
                'remove_avatar' => 'boolean',
            ];
        } else {
            return [
                'name' => 'string|max:255',
                'email' => ['email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user'))],
                'role' => [new Enum(Roles::class)],
                'phone' => 'nullable|string|max:255',
                'age' => 'nullable|integer|min:0|max:150',
                'language' => ['nullable', new Enum(Languages::class)],
                'currency' => ['nullable', new Enum(Currencies::class)],
                'avatar' => User::fileRules('avatar'),
                'remove_avatar' => 'boolean',
            ];
        }
    }
}
