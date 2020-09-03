<?php

use App\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $firstDayOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $fifthDayOfMonth = Carbon::now()->startOfMonth()->addDays(4)->format('Y-m-d');
        $fifteenthOfMonth = Carbon::now()->startOfMonth()->addDays(14)->format('Y-m-d');

        factory(Transaction::class)->create([
            'category_id' => 1, //sales
            'date'        => $firstDayOfMonth,
            'amount'      => 20000
        ]);

        factory(Transaction::class)->create([
            'category_id' => 1,
            'date'        => $firstDayOfMonth,
            'amount'      => 15000
        ]);

        factory(Transaction::class)->create([
            'category_id' => 4, //electric bill
            'date'        => $firstDayOfMonth,
            'amount'      => 17500
        ]);

        factory(Transaction::class)->create([
            'category_id' => 1,
            'date'        => $fifthDayOfMonth,
            'amount'      => 10000
        ]);

        factory(Transaction::class)->create([
            'category_id' => 1,
            'date'        => $firstDayOfMonth,
            'amount'      => 20000
        ]);

        factory(Transaction::class)->create([
            'category_id' => 5, // internet
            'date'        => $firstDayOfMonth,
            'amount'      => 12000
        ]);

        factory(Transaction::class)->create([
            'category_id' => 1,
            'date'        => $fifteenthOfMonth,
            'amount'      => 35000
        ]);

        factory(Transaction::class)->create([
            'category_id' => 6, // water
            'date'        => $firstDayOfMonth,
            'amount'      => 10000
        ]);

        factory(Transaction::class)->create([
            'category_id' => 7, // fuel
            'date'        => $firstDayOfMonth,
            'amount'      => 5000
        ]);
    }
}
