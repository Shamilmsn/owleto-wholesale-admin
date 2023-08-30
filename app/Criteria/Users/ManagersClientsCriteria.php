<?php
/**
 * File name: ManagersClientsCriteria.php
 * Last modified: 2020.06.15 at 20:15:26
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Criteria\Users;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class ClientCriteria.
 *
 * @package namespace App\Criteria\Users;
 */
class ManagersClientsCriteria implements CriteriaInterface
{
    /**
     * Apply criteria in query repository
     *
     * @param string $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        return $model->whereHas("roles", function ($q) {
            $q->where('name', '<>', 'admin');


        });
    }
}
