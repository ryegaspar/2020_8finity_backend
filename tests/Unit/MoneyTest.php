<?php

namespace Tests\Unit;

use App\ExpenseTracker\Money;
use Money\Money as BaseMoney;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    /** @test */
    public function it_can_return_the_underlying_instance()
    {
        $money = new Money(1000);

        $this->assertInstanceOf(BaseMoney::class, $money->instance());
    }

    /** @test */
    public function it_can_get_the_raw_amount()
    {
        $money = new Money(1000);

        $this->assertEquals(1000, $money->amount());
    }

    /** @test */
    public function it_can_get_the_formatted_amount()
    {
        $money = new Money(1000);

        $this->assertEquals('₱10.00', $money->formatted());
    }

    /** @test */
    public function it_can_add_up()
    {
        $money = new Money(1000);

        $money = $money->add(new Money(500));

        $this->assertEquals(1500, $money->amount());
    }
}
