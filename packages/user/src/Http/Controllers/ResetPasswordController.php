<?php

namespace GGPHP\User\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    protected function sendResetResponse($response)
    {
        return $this->success([], trans('lang::messages.auth.resetPasswordSuccess'), false);
    }

    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string',
        ];
    }

    protected function sendResetFailedResponse(Request $request, $response)
    {
        return $this->error(trans('lang::messages.auth.resetPasswordFail'), trans($response), config('constants.HTTP_STATUS_CODE.BAD_REQUEST'));
    }
}
