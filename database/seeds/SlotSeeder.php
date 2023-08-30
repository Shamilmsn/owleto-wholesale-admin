<?php

namespace Database\Seeders;

use App\Models\Slot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('slots')->truncate();

        $startAt = env('SLOT_START_AT');
        $endAt = env('SLOT_START_END');
        $interVal = env('SLOT_INTERVAL');

        $from = Str::of($startAt)->before(':');
        $to = Str::of($interVal)->after(':');
        $end = Str::of($endAt)->before(':');

        while($from < $end){

            $slot = new Slot();
            $slot->from_hour = Str::of($startAt)->before(':');
            $slot->from_minute = Str::of($startAt)->after(':');

            $startAt = date('H:i', strtotime($startAt. '+'.$to.' minutes'));

            $slot->end_hour = Str::of($startAt)->before(':');
            $slot->end_minute = Str::of($startAt)->after(':');
            $slot->save();

            $from = Str::of($startAt)->before(':');

        }
    }
}
