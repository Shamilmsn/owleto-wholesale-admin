<?php

namespace Database\Seeders;

use App\Models\TShirtSize;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TShirtSizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('t_shirt_sizes')->truncate();

        $tshirtSize = new TShirtSize();
        $tshirtSize->name = 'XS';
        $tshirtSize->save();

        $tshirtSize = new TShirtSize();
        $tshirtSize->name = 'S';
        $tshirtSize->save();

        $tshirtSize = new TShirtSize();
        $tshirtSize->name = 'M';
        $tshirtSize->save();

        $tshirtSize = new TShirtSize();
        $tshirtSize->name = 'L';
        $tshirtSize->save();

        $tshirtSize = new TShirtSize();
        $tshirtSize->name = 'XL';
        $tshirtSize->save();

        $tshirtSize = new TShirtSize();
        $tshirtSize->name = 'XXL';
        $tshirtSize->save();
    }
}
