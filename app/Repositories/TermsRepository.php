<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\Term;
use Prettus\Repository\Contracts\CacheableInterface;
use Prettus\Repository\Traits\CacheableRepository;

/**
 * Class ProductRepository
 * @package App\Repositories
 * @version August 29, 2019, 9:38 pm UTC
 *
 * @method Product findWithoutFail($id, $columns = ['*'])
 * @method Product find($id, $columns = ['*'])
 * @method Product first($columns = ['*'])
 */
class TermsRepository extends BaseRepository implements CacheableInterface
{
    use CacheableRepository;

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Term::class;
    }
}
