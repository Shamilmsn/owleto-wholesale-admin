<?php
/**
 * File name: CategoriesOfFieldsCriteria.php
 * Last modified: 2020.05.04 at 09:04:18
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Criteria\DriverTransactions;

use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class CategoriesOfFieldsCriteria.
 *
 * @package namespace App\Criteria\Categories;
 */
class DriverTransactionOfUsers implements CriteriaInterface
{

    /**
     * @var array
     */
    private $request;

    /**
     * CategoriesOfFieldsCriteria constructor.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

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
        return $model->where('user_id', $this->request->get('user_id'));
    }
}
