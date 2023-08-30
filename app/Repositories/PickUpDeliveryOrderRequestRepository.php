<?php

namespace App\Repositories;

use App\Models\Attribute;
use App\Models\OptionGroup;
use App\Models\PickUpDeliveryOrderRequest;
use App\Models\PickUpVehicle;

//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class OptionGroupRepository
 * @package App\Repositories
 * @version April 6, 2020, 10:47 am UTC
 *
 * @method OptionGroup findWithoutFail($id, $columns = ['*'])
 * @method OptionGroup find($id, $columns = ['*'])
 * @method OptionGroup first($columns = ['*'])
*/
class PickUpDeliveryOrderRequestRepository extends BaseRepository
{

    /**
     * Configure the Model
     **/
    public function model()
    {
        return PickUpDeliveryOrderRequest::class;
    }
}
