<?php

namespace GGPHP\User\Http\Controllers;

use App\Http\Controllers\Controller;
use GGPHP\User\Http\Requests\UserCreateRequest;
use GGPHP\User\Http\Requests\UserUpdateRequest;
use GGPHP\User\Repositories\Contracts\UserRepository;
use GGPHP\User\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    use ResponseTrait;

    /**
     * @var $userRepository
     */
    protected $userRepository;

    /**
     * UserController constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $limit = config('constants.SEARCH_VALUES_DEFAULT.LIMIT');
        if ($request->has('limit')) {
            $limit = $request->limit;
        }

        if ($limit == config('constants.SEARCH_VALUES_DEFAULT.LIMIT_ZERO')) {
            $users = $this->userRepository->all();
        } else {
            $users = $this->userRepository->paginate($limit);
        }

        return $this->success($users, trans('lang-user::messages.common.getListSuccess'));
    }

    /**
     *
     * @param UserCreateRequest $request
     *
     * @return Response
     */
    public function store(UserCreateRequest $request)
    {
        $credentials = $request->all();

        if (!empty($credentials['password'])) {
            $credentials['password'] = bcrypt($credentials['password']);
        }
        $user = $this->userRepository->create($credentials);

        return $this->success($user, trans('lang-user::messages.auth.createSuccess'), ['code' => Response::HTTP_CREATED]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function show(Request $request, $id)
    {
        $user = $this->userRepository->find($id);

        return $this->success($user, trans('lang-user::messages.common.getInfoSuccess'));
    }

    /**
     *
     * @param UserUpdateRequest $request
     * @param  string $id
     *
     * @return Response
     */
    public function update(UserUpdateRequest $request, $id)
    {
        $credentials = $request->all();

        if (!empty($credentials['password'])) {
            $credentials['password'] = bcrypt($credentials['password']);
        }
        $user = $this->userRepository->update($credentials, $id);

        return $this->success($user, trans('lang-user::messages.common.modifySuccess'));
    }

    /**
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $this->userRepository->delete($id);
        return $this->success([], trans('lang-user::messages.common.deleteSuccess'));
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        $this->userRepository->restore($id);

        return $this->success([], trans('lang::messages.common.deleteSuccess'), ['code' => Response::HTTP_NO_CONTENT, 'isShowData' => false]);
    }
}
