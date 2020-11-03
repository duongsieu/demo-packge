<?php

namespace GGPHP\User\Http\Controllers;

use App\Http\Controllers\Controller;
use GGPHP\User\Traits\ResponseTrait;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use GGPHP\User\Http\Requests\ForgotPasswordRequest;

class ResetPasswordController extends Controller
{
    use ResetsPasswords, ResponseTrait;

    protected function sendResetResponse($response)
    {
        return $this->success([], trans('lang::messages.auth.resetPasswordSuccess'), false);
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function reset(ForgotPasswordRequest $request)
    {

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = $this->broker()->reset(
            $this->credentials($request),
            function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $response == Password::PASSWORD_RESET
            ? $this->sendResetResponse($request, $response)
            : $this->sendResetFailedResponse($request, $response);
    }

    protected function sendResetFailedResponse(Request $request, $response)
    {
        return $this->error(trans('lang::messages.auth.resetPasswordFail'), trans($response), config('constants.HTTP_STATUS_CODE.BAD_REQUEST'));
    }
}
