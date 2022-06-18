<?php

namespace App\Domains\Auth\Http\Requests\Backend\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use LangleyFoxall\LaravelNISTPasswordRules\PasswordRules;

/**
 * Class StoreUserRequest.
 */
class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => ['required', 'email', Rule::unique('users')],
            'password' => PasswordRules::register($this->email),
            'active' => ['sometimes', 'in:1'],
            'email_verified' => ['sometimes', 'in:1'],
            'send_confirmation_email' => ['sometimes', 'in:1'],
            'company' => ['required'],
            'customer_id' => ['required'],
            'roles' => ['required', 'array'],
            'roles.*' => [Rule::exists('roles', 'id')],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => [Rule::exists('permissions', 'id')],
            'is_download_draft' => '',
            'is_download_project' => ''
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'roles.required' => __('You must select one or more roles.'),
            'company.required' => __('You must select one company.'),
        ];
    }
}
