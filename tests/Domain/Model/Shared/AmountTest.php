<?php

namespace Domain\Model\Shared;

use App\Domain\Model\Shared\Amount\Amount;
use PHPUnit\Framework\TestCase;

class AmountTest extends TestCase
{
    public function testZeroAmountIsCreatedFromEmptyString(): void
    {
        $amount = Amount::fromString("");

        $this->assertEquals(0, $amount->getValue());
        $this->assertEquals(0, $amount->getDecimals());
        $this->assertTrue($amount->isZero());
    }

    public function testAmountIsCreatedFromStringContainingOnlyIntegerPart(): void
    {
        $amount = Amount::fromString("1234");

        $this->assertEquals(1234, $amount->getValue());
        $this->assertEquals(0, $amount->getDecimals());
    }

    public function testAmountIsCreatedFromStringContainingOnlyDecimals(): void
    {
        $amount = Amount::fromString("0.1234");

        $this->assertEquals(1234, $amount->getValue());
        $this->assertEquals(4, $amount->getDecimals());
    }

    public function testAmountIsCreatedFromStringContainingIntegerAndDecimals(): void
    {
        $amount = Amount::fromString("123.4");

        $this->assertEquals(1234, $amount->getValue());
        $this->assertEquals(1, $amount->getDecimals());
    }

    public function testAmountIsCreatedFromStringWithOnlyZeros(): void
    {
        $amount = Amount::fromString("000.000");

        $this->assertEquals(0, $amount->getValue());
        $this->assertEquals(0, $amount->getDecimals());
    }

    public function testAmountIsCreatedFromStringWithOnlyZerosInDecimalPart(): void
    {
        $amount = Amount::fromString("123.000");

        $this->assertEquals(123, $amount->getValue());
        $this->assertEquals(0, $amount->getDecimals());
    }

    public function testAmountIsCreatedFromStringWithCommas(): void
    {
        $amount = Amount::fromString("12,345,678");

        $this->assertEquals(12345678, $amount->getValue());
        $this->assertEquals(0, $amount->getDecimals());
    }

    public function testAmountIsCreatedFromStringWithCommasAndDots(): void
    {
        $amount = Amount::fromString("12,345.678");

        $this->assertEquals(12345678, $amount->getValue());
        $this->assertEquals(3, $amount->getDecimals());
    }

    public function testAmountIsCreatedFromStringWithNegativeValue(): void
    {
        $amount = Amount::fromString("-123.45");

        $this->assertEquals(-12345, $amount->getValue());
        $this->assertEquals(2, $amount->getDecimals());
    }

    public function testTrailingDecimalZerosAreRemoved(): void
    {
        $amount = Amount::fromString("123.45000");

        $this->assertEquals(12345, $amount->getValue());
        $this->assertEquals(2, $amount->getDecimals());
    }

    public function testFloatAmountReturnsCorrectNumericValue(): void
    {
        $amount = Amount::fromString("123.45");
        $this->assertIsFloat($amount->toNumber());
        $this->assertEqualsWithDelta(123.45, $amount->toNumber(), 1e-10);
    }

    public function testIntegerAmountReturnsCorrectNumericValue(): void
    {
        $amount = Amount::fromString("123");
        $this->assertIsInt($amount->toNumber());
        $this->assertEquals(123, $amount->toNumber());
    }

    public function testFloatAmountReturnsCorrectStringValue(): void
    {
        $amount = Amount::fromString("123.45");
        $this->assertEquals("123.45", $amount->toString());
    }

    public function testIntAmountReturnsCorrectStringValue(): void
    {
        $amount = Amount::fromString("123");
        $this->assertEquals("123", $amount->toString());
    }

    public function testSumTwoPositiveValues(): void
    {
        $a1 = Amount::fromString("12.345");
        $a2 = Amount::fromString("543.21");

        $sum = Amount::sum($a1, $a2);

        $this->assertEquals(555555, $sum->getValue());
        $this->assertEquals(3, $sum->getDecimals());
    }

    public function testSumPositiveAndNegativeValues(): void
    {
        $a1 = Amount::fromString("12.34");
        $a2 = Amount::fromString("-15");

        $sum = Amount::sum($a1, $a2);

        $this->assertEquals(-266, $sum->getValue());
        $this->assertEquals(2, $sum->getDecimals());
    }

    public function testSumTwoNegativeValues(): void
    {
        $a1 = Amount::fromString("-12.34");
        $a2 = Amount::fromString("-15.1044");

        $sum = Amount::sum($a1, $a2);

        $this->assertEquals(-274444, $sum->getValue());
        $this->assertEquals(4, $sum->getDecimals());
    }

    public function testSubtractTwoPositiveValues(): void
    {
        $a1 = Amount::fromString("12.3");
        $a2 = Amount::fromString("7.21");

        $sum = Amount::subtract($a1, $a2);

        $this->assertEquals(509, $sum->getValue());
        $this->assertEquals(2, $sum->getDecimals());
    }

    public function testSubtractPositiveAndNegativeValues(): void
    {
        $a1 = Amount::fromString("12.3");
        $a2 = Amount::fromString("-7.21");

        $sum = Amount::subtract($a1, $a2);

        $this->assertEquals(1951, $sum->getValue());
        $this->assertEquals(2, $sum->getDecimals());
    }

    public function testSubtractTwoNegativeValues(): void
    {
        $a1 = Amount::fromString("-12.3");
        $a2 = Amount::fromString("-7.21");

        $sum = Amount::subtract($a1, $a2);

        $this->assertEquals(-509, $sum->getValue());
        $this->assertEquals(2, $sum->getDecimals());
    }
}