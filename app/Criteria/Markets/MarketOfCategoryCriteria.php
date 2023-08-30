<?php

namespace App\Criteria\Markets;


use App\Models\Market;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class MarketsOfFieldsCriteria.
 *
 * @package namespace App\Criteria\Markets;
 */
class MarketOfCategoryCriteria implements CriteriaInterface
{
    /**
     * @var array
     */
    private $request;

    /**
     * MarketsOfFieldsCriteria constructor.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    /**
     * Apply criteria in query repository
     *
     * @param string              $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        if(!$this->request->has('category_id')) {
            return $model;
        } else {
            $category_id = $this->request->get('category_id');
            // return $model->where('field_id', $field_id);

            return $model->join('market_categories', 'market_categories.market_id', '=', 'markets.id')
                ->where('market_categories.category_id', $category_id)->groupBy('markets.id');
        }
    }
}
