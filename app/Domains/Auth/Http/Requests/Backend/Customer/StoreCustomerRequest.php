<?php

namespace App\Domains\Auth\Http\Requests\Backend\Customer;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreCustomerRequest.
 */
class StoreCustomerRequest extends FormRequest
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
            'name' => ['required'],
            'logo' => ['required'],
        ];
    }
}
