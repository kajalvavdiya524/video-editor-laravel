<?php

namespace App\Domains\Auth\Http\Requests\Backend\Template;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class DeleteTemplateRequest.
 */
class DeleteTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return (auth()->user()->isMasterAdmin() || $this->template->company_id == auth()->user()->company_id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
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
        throw new AuthorizationException(__('You can not delete the template.'));
    }
}
