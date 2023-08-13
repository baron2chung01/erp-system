<?php

namespace App\Http\Requests\API;

use App\Models\OutlineAgreement;
use InfyOm\Generator\Request\APIRequest;

class UpdateOutlineAgreementAPIRequest extends APIRequest
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
        $rules = OutlineAgreement::$rules;
        
        return $rules;
    }
}
