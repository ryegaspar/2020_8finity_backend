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
        $today = Carbon::now()->format('Y-m-d');
        $twoDaysAgo = Carbon::now()->subDay()->format('Y-m-d');
        $sevenDaysAgo = Carbon::now()->subDays(7)->format('Y-m-d');
        $lastMonth = Carbon::now()->subMonth()->format('Y-m-d');

        factory(Transaction::class)->create([
            'category_id' => 1, //sales
            'date'        => $today,
            'amount'      => 20000
        ]);

        factory(Transaction::class)->create([
            'category_id' => 1,
            'date'        => $today,
            'amount'      => 15000
        ]);

        factory(Transaction::class)->create([
            'category_id' => 4, //electric bill
            'date'        => $today,
            'amount'      => 17500
        ]);

        factory(Transaction::class)->create([
            'category_id' => 5, // internet
            'date'        => $today,
            'amount'      => 12000
        ]);

        factory(Transaction::class)->create([
            'category_id' => 6, // water
            'date'        => $today,
            'amount'      => 10000
        ]);

        factory(Transaction::class)->create([
            'category_id' => 7, // fuel
            'date'        => $today,
            'amount'      => 5000
        ]);

        factory(Transaction::class)->create([
            'category_id' => 1,
            'date'        => $twoDaysAgo,
            'amount'      => 90000
        ]);

        factory(Transaction::class)->create([
            'category_id' => 7, // fuel
            'date'        => $twoDaysAgo,
            'amount'      => 30000
        ]);


        factory(Transaction::class)->create([
            'category_id' => 1,
            'date'        => $sevenDaysAgo,
            'amount'      => 10000
        ]);

        factory(Transaction::class)->create([
            'category_id' => 1,
            'date'        => $sevenDaysAgo,
            'amount'      => 20000
        ]);

        factory(Transaction::class)->create([
            'category_id' => 1,
            'date'        => $lastMonth,
            'amount'      => 35000
        ]);

        factory(Transaction::class)->create([
            'category_id' => 7,
            'date'        => $lastMonth,
            'amount'      => 55000
        ]);
    }
}
