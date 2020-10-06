<?php

namespace GGPHP\User\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use GGPHP\User\Models\User;
use GGPHP\User\Repositories\Contracts\UserRepository;
use GGPHP\User\Traits\ResponseTrait;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\TokenRepository;
use Lcobucci\JWT\Parser as JwtParser;
use League\OAuth2\Server\AuthorizationServer;
use Psr\Http\Message\ServerRequestInterface;
use Response;
use Validator;
use \Laravel\Passport\Http\Controllers\AccessTokenController as ATController;

class AuthController extends ATController
{
    use ResponseTrait;

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

        $validator = Validator::make($request->getParsedBody(), [
            'username' => [
                function ($attribute, $value, $fail) {
                    $user = User::where('email', $value)->orWhere('user_name', $value)->first();
                    if ($user) {
                        return true;
                    }
                    return $fail('The selected is invalid.');
                },
            ],
            'password' => 'required',
        ]
        );

        if ($validator->fails()) {
            $error = $validator->errors()->toArray();
            $result = [];
            foreach ($error as $key => $value) {
                $result[] = [
                    "title" => "Validation Error.",
                    "detail" => $value[0],
                    "source" => [
                        "pointer" => $key,
                    ],
                ];
            }

            return response()->json(([
                "status" => 400,
                "title" => "Validation Error.",
                'errors' => $result,
            ]), 400);
        }

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
    public function changePassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|confirmed',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->toArray();
            $result = [];
            foreach ($error as $key => $value) {
                $result[] = [
                    "title" => "Validation Error.",
                    "detail" => $value[0],
                    "source" => [
                        "pointer" => $key,
                    ],
                ];
            }

            return response()->json(([
                "status" => 400,
                "title" => "Validation Error.",
                'errors' => $result,
            ]), 400);
        }

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
    public function signUp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'email|unique:users,email',
            'user_name' => 'unique:users,user_name',
            'name' => 'required|string',
            'password' => [
                "required",
                'confirmed',
                'regex:/^.*(?=.*[A-Z])(?=.*[0-9])(?=.*[a-z].*[a-z].*[a-z])(?=.*[!$#@^&*.%]).{6}$/',
            ],
        ],
            [
                'password.regex' => 'Password must have 6 characters including capital letters, special characters, numbers',
            ]
        );

        if ($validator->fails()) {
            $error = $validator->errors()->toArray();
            $result = [];
            foreach ($error as $key => $value) {
                $result[] = [
                    "title" => "Validation Error.",
                    "detail" => $value[0],
                    "source" => [
                        "pointer" => $key,
                    ],
                ];
            }

            return response()->json(([
                "status" => 400,
                "title" => "Validation Error.",
                'errors' => $result,
            ]), 400);
        }

        $credentials = $request->all();

        if (!empty($credentials['password'])) {
            $credentials['password'] = bcrypt($credentials['password']);
        }
        $user = $this->userRepository->create($credentials);
        return $this->success($user, trans('lang-user::messages.auth.createSuccess'), ['code' => Response::HTTP_CREATED]);
    }

}
