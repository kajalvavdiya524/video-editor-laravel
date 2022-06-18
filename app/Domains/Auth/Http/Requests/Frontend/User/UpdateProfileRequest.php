<?php

namespace App\Domains\Auth\Http\Requests\Frontend\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateProfileRequest.
 */
class UpdateProfileRequest extends FormRequest
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
            'first_name' => 'required',
            'last_name' => 'required',
            'customer_id' => 'required',
            'email' => [Rule::requiredIf(function () {
                return config('boilerplate.access.user.change_email');
            }), 'email', Rule::unique('users')->ignore($this->user()->id)],
            'is_download_draft' => '',
            'is_download_project' => ''
        ];
    }
}
