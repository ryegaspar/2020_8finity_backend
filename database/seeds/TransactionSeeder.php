<?php

use App\Models\Transaction;
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

        Transaction::factory()->create([
            'category_id' => 1, //sales
            'admin_id'    => 1,
            'date'        => $today,
            'amount'      => 20000
        ]);

        Transaction::factory()->create([
            'category_id' => 1,
            'admin_id'    => 1,
            'date'        => $today,
            'amount'      => 15000
        ]);

        Transaction::factory()->create([
            'category_id' => 4, //electric bill
            'admin_id'    => 1,
            'date'        => $today,
            'amount'      => 17500
        ]);

        Transaction::factory()->create([
            'category_id' => 5, // internet
            'admin_id'    => 1,
            'date'        => $today,
            'amount'      => 12000
        ]);

        Transaction::factory()->create([
            'category_id' => 6, // water
            'admin_id'    => 1,
            'date'        => $today,
            'amount'      => 10000
        ]);

        Transaction::factory()->create([
            'category_id' => 7, // fuel
            'admin_id'    => 1,
            'date'        => $today,
            'amount'      => 5000
        ]);

        Transaction::factory()->create([
            'category_id' => 1,
            'admin_id'    => 1,
            'date'        => $twoDaysAgo,
            'amount'      => 90000
        ]);

        Transaction::factory()->create([
            'category_id' => 7, // fuel
            'admin_id'    => 1,
            'date'        => $twoDaysAgo,
            'amount'      => 30000
        ]);


        Transaction::factory()->create([
            'category_id' => 1,
            'admin_id'    => 1,
            'date'        => $sevenDaysAgo,
            'amount'      => 10000
        ]);

        Transaction::factory()->create([
            'category_id' => 1,
            'admin_id'    => 1,
            'date'        => $sevenDaysAgo,
            'amount'      => 20000
        ]);

        Transaction::factory()->create([
            'category_id' => 1,
            'admin_id'    => 1,
            'date'        => $lastMonth,
            'amount'      => 35000
        ]);

        Transaction::factory()->create([
            'category_id' => 7,
            'admin_id'    => 1,
            'date'        => $lastMonth,
            'amount'      => 55000
        ]);
    }
}
