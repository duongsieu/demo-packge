<?php

namespace GGPHP\User\Repositories\Eloquent;

use GGPHP\User\Models\User;
use GGPHP\User\Presenters\UserPresenter;
use GGPHP\User\Repositories\Contracts\UserRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Class UserRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class UserRepositoryEloquent extends BaseRepository implements UserRepository
{

    /**
     * @var array
     */
    protected $fieldSearchable = [
        'id',
        'name' => 'like',
        'email' => 'like',
        'user_name' => 'like',
    ];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return User::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    /**
     * Specify Presenter class name
     *
     * @return string
     */
    public function presenter()
    {
        return UserPresenter::class;
    }

    public function delete($id)
    {
        $model = $this->model->withTrashed()->findOrFail($id);

        if (request()->force === true) {
            $model->forceDelete();
        }

        $model->delete();
    }

    public function restore($id)
    {
        $model = $this->model->withTrashed()->findOrFail($id);

        $model->restore();
    }

}
