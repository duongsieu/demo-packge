<?php

namespace GGPHP\User\Http\Requests;

use Illuminate\Validation\Rule;

class UserUpdateRequest extends BaseRequest
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
            'email' => [
                "email",
                Rule::unique('users')->ignore($id),
            ],
            'user_name' => [
                Rule::unique('users')->ignore($id),
            ],
            'password' => 'regex:/^.*(?=.*[A-Z])(?=.*[0-9])(?=.*[a-z])(?=.*[!$#%]).{6}$/',
        ];
    }
}
