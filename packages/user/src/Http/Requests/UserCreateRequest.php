<?php

namespace GGPHP\User\Http\Requests;

class UserCreateRequest extends BaseRequest
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
            'email' => 'email|unique:users,email',
            'user_name' => 'unique:users,user_name',
            'name' => 'required|string',
            'password' => [
                "required",
                "regex:/^.*(?=.*[A-Z])(?=.*[0-9])(?=.*[a-z].*[a-z].*[a-z])(?=.*[!$#@^&*.%]).{6}$/",
            ],
        ];
    }
}
