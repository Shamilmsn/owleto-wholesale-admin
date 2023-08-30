<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\UserWallet;

/**
 * Class UserRepository
 * @package App\Repositories
 * @version July 10, 2018, 11:44 am UTC
 *
 * @method User findWithoutFail($id, $columns = ['*'])
 * @method User find($id, $columns = ['*'])
 * @method User first($columns = ['*'])
*/
class UserWalletRepository extends BaseRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return UserWallet::class;
    }
}
