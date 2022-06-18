<?php

namespace App\Domains\Auth\Http\Requests\Backend\ApiKeys;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use LangleyFoxall\LaravelNISTPasswordRules\PasswordRules;

/**
 * Class StoreApiKeysRequest.
 */
class StoreApiKeysRequest extends FormRequest
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
            'key' => ['required', 'min:40','regex:/^[a-zA-Z0-9 ]+$/'],
            'company' => [],
        ];
    }
}
