<?php

namespace App\Repositories;

use App\Models\MerchantRequest;
use Prettus\Repository\Contracts\CacheableInterface;
use Prettus\Repository\Traits\CacheableRepository;

/**
 * Class MarketRepository
 * @package App\Repositories
 * @version August 29, 2019, 9:38 pm UTC
 *
 * @method MerchantRequest findWithoutFail($id, $columns = ['*'])
 * @method MerchantRequest find($id, $columns = ['*'])
 * @method MerchantRequest first($columns = ['*'])
 */
class MerchantRequestRepository extends BaseRepository implements CacheableInterface
{

    use CacheableRepository;

    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'description',
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return MerchantRequest::class;
    }

}
