<?php

namespace GGPHP\User\Http\Controllers;

use Carbon\Carbon;
use GGPHP\User\Http\Requests\ChangePasswordRequest;
use GGPHP\User\Http\Requests\ResetPasswordRequest;
use GGPHP\User\Http\Requests\UserSignUpRequest;
use GGPHP\User\Mail\ResetPassword;
use GGPHP\User\Models\User;
use GGPHP\User\Repositories\Contracts\UserRepository;
use GGPHP\User\Traits\ResponseTrait;
use Hash;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\TokenRepository;
use Lcobucci\JWT\Parser as JwtParser;
use League\OAuth2\Server\AuthorizationServer;
use Mail;
use Psr\Http\Message\ServerRequestInterface;
use \Laravel\Passport\Http\Controllers\AccessTokenController as ATController;

class AuthController extends ATController
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

    public function __construct(AuthorizationServer $server, TokenRepository $tokens, JwtParser $jwt, UserRepository $userRepository)
    {
        parent::__construct($server, $tokens, $jwt);

        $this->userRepository = $userRepository;
    }

    /**
     * authenticated
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function authenticated(Request $request)
    {

        $user = $this->userRepository->find(Auth::id());
        return $this->success($user, trans('lang-user::messages.common.getInfoSuccess'));
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function logout(Request $request)
    {

        $request->user()->token()->revoke();

        return $this->success([], trans('lang-user::messages.auth.logoutSuccess'), ['isShowData' => false]);
    }

    public function login(ServerRequestInterface $request)
    {
        $attributes = $request->getParsedBody();

        try {
            $username = $request->getParsedBody()['username'];

            $user = User::where('email', $username)->orWhere('user_name', $username)->first();
            //generate token
            $tokenResponse = parent::issueToken($request);

            //convert response to json string
            $content = $tokenResponse->getContent();
            $data = json_decode($content, true);
            if (isset($data['error'])) {
                throw new OAuthServerException('The user credentials were incorrect.', 6, 'invalid_credentials', 401);
            }

            if (!empty($request->getParsedBody()['player_id'])) {
                $this->userRepository->addPlayer($request->getParsedBody()['player_id'], $user->id);
            }

            return Response::json(collect($data));
        } catch (ModelNotFoundException $e) {
            // email notfound
            if ($e instanceof ModelNotFoundException) {
                return $this->error('Invalid_credentials', 'User does not exist. Please try again', 400);
            }
        } catch (OAuthServerException $e) {

            //password not correct..token not granted
            return $this->error('Invalid_credentials', 'Password is not correct', 401);
        } catch (Exception $e) {

            return response(['error' => 'unsupported_grant_type', 'message' => 'The authorization grant type is not supported by the authorization server.', 'hint' => 'Check that all required parameters have been provided'], 400);
        }
    }

    /**
     * Change password
     *
     * @param ChangePasswordRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->error(trans('lang::messages.auth.changePasswordFail'), trans('lang::messages.auth.currentPasswordNotMatch'), config('constants.HTTP_STATUS_CODE.BAD_REQUEST'));
        }

        $this->userRepository->update(['password' => Hash::make($request->password)], Auth::id());
        $user->token()->revoke();

        $objToken = $user->createToken('name');
        // return new token
        $dataSuccess = [
            'type' => 'Token',
            'attributes' => [
                'id' => $objToken->token->id,
                'access_token' => $objToken->accessToken,
                'token_type' => config('constants.TOKEN.TYPE'),
                'expires_in' => Carbon::parse($objToken->token->expires_at)->toDateTimeString(),
            ],
        ];

        return $this->success($dataSuccess, trans('lang::messages.auth.changePasswordSuccess'));
    }

    /**
     *
     * @param UserCreateRequest $request
     *
     * @return Response
     */
    public function signUp(UserSignUpRequest $request)
    {
        $credentials = $request->all();

        if (!empty($credentials['password'])) {
            $credentials['password'] = bcrypt($credentials['password']);
        }

        $user = $this->userRepository->create($credentials);

        return $this->success($user, trans('lang-user::messages.auth.createSuccess'), ['code' => Response::HTTP_CREATED]);
    }

    /**
     * @param ResetPasswordRequest $request
     * @return \Illuminate\Http\Response
     */
    public function getResetToken(ResetPasswordRequest $request)
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
            return $this->error(trans('lang::messages.auth.resetPasswordFail'), $e->getMessage(), config('constants.HTTP_STATUS_CODE.SERVER_ERROR'));
        }
        return $this->success([], trans('lang::messages.auth.sendLinkResetPasswordSuccess'), false);
    }
}
