<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class FlashSaleUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flash:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $products = Product::query()
            ->where('is_flash_sale', 1)
            ->where('flash_sale_end_time','<', Carbon::now()->format('Y-m-d H:i:s'))
            ->get();

        if (count($products) > 0) {
            foreach ($products as $product) {
                $product->is_flash_sale = 0;
                $product->flash_sale_end_time = null;
                $product->flash_sale_start_time = null;
                $product->flash_sale_price = null;
                $product->save();
            }
        }
    }
}
