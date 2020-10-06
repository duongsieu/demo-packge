<?php

namespace GGPHP\User\Presenters;

use GGPHP\User\Transformers\UserTransformer;
use Prettus\Repository\Presenter\FractalPresenter;

/**
 * Class UserPresenter.
 *
 * @package namespace App\Presenters;
 */
class UserPresenter extends FractalPresenter
{
    /**
     * @var string
     */
    public $resourceKeyItem = 'User';

    /**
     * @var string
     */
    public $resourceKeyCollection = 'User';

    /**
     * Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return new UserTransformer();
    }
}
