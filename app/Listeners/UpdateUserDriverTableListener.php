<?php

namespace App\Listeners;

use App\Repositories\DriverRepository;
use Prettus\Validator\Exceptions\ValidatorException;

class UpdateUserDriverTableListener
{
    /**
     * @var DriverRepository
     */
    private $driverRepository;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(DriverRepository $driverRepository)
    {
        //
        $this->driverRepository = $driverRepository;
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $uniqueInput = ['user_id'=>$event->user->id];
        if($event->user->hasRole('driver')){
            try {
                $this->driverRepository->updateOrCreate($uniqueInput);
//                $driver = $this->driverRepository->first();
//                $driver->delivery_fee = $event->deliveryFee;
//                $driver->save();
            } catch (ValidatorException $e) {
            }
        }
    }
}
