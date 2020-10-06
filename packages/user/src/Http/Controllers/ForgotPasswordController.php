<?php

namespace GGPHP\User\Http\Controllers;

use App\Http\Controllers\Controller;
use GGPHP\User\Http\Requests\ResetPasswordRequest;
use GGPHP\User\Mail\ResetPassword;
use GGPHP\User\Repositories\Contracts\UserRepository;
use GGPHP\User\Traits\ResponseTrait;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Mail;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails, ResponseTrait;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * UserController constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param ResetPasswordRequest $request
     * @return \Illuminate\Http\Response
     */
    public function getResetToken(Request $request)
    {
        $this->userRepository->skipPresenter();
        $email = $request->email;
        $user = $this->userRepository->findByField('email', $email)->first();

        // create reset password token
        $token = $this->broker()->createToken($user);
        // send mail
        $email = $user->email;
        $name = $user->name;
        $domainClient = env('RESET_PASSWORD_URL', 'http://localhost/password/reset');
        $urlClient = $domainClient . '/' . $token;
        try {

            Mail::to($email, $name)->send(new ResetPassword(compact('name', 'urlClient')));
        } catch (\Exception $e) {
            dd($e);
            return $this->error(trans('lang::messages.auth.resetPasswordFail'), $e->getMessage(), config('constants.HTTP_STATUS_CODE.SERVER_ERROR'));
        }
        return $this->success([], trans('lang::messages.auth.sendLinkResetPasswordSuccess'), false);
    }
}
