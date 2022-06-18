<?php

namespace App\Domains\Auth\Http\Requests\Backend\User;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateUserRequest.
 */
class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return ! ($this->user->isMasterAdmin() && ! $this->user()->isMasterAdmin());
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
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->user->id)],
            'company' => ['required'],
            'can_upload_image' => ['required'],
            'customer_id' => ['required'],
            'roles' => [Rule::requiredIf(function () {
                return ! $this->user->isMasterAdmin();
            }), 'array'],
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

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function failedAuthorization()
    {
        throw new AuthorizationException(__('Only the administrator can update this user.'));
    }
}
