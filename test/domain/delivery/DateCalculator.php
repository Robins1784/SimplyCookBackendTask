<?php

declare(strict_types=1);

namespace Test\Domain\Delivery;

use Domain\Delivery\BillingBreak;
use Domain\Delivery\DateCalculator as Calculator;
use Domain\Delivery\Frequency;
use PHPUnit\Framework\TestCase;

class DateCalculator extends TestCase
{
    private const LeadTime = 2;

    public function testInitialOrderBeforeCutoffWillArriveAfterLeadTimeAndNoOtherModification()
    {
        $mondayNineAm = new \DateTimeImmutable('12-12-2022 09:00:00');
        $deliveryTime = (new Calculator(
            customerFrequency: Frequency::weekly,
            leadTime: self::LeadTime,
            processedAt: $mondayNineAm,
            lastBilledAt: null
        ))->getProjectedDeliveryDate();

        $this->assertEquals(new \DateTimeImmutable('14-12-2022 00:00:00'), $deliveryTime);
    }

    public function testInitialOrderAfterCutoffWithNoBreakHasOneDayAddedToDelivery()
    {
        $mondayThreePm = new \DateTimeImmutable('12-12-2022 15:00');
        $deliveryTime = (new Calculator(
            customerFrequency: Frequency::weekly,
            leadTime: self::LeadTime,
            processedAt: $mondayThreePm,
            lastBilledAt: null
        ))->getProjectedDeliveryDate();

        $this->assertEquals(new \DateTimeImmutable('15-12-2022 00:00'), $deliveryTime);
    }

    public function testInitialOrderWithBreakAfterCutoffWillBeDeliveredAsNormal()
    {
        $mondayThreePm = new \DateTimeImmutable('12-12-2022 15:00');
        $oneWeekBreak = new BillingBreak(1);
        $deliveryTime = (new Calculator(
            customerFrequency: Frequency::weekly,
            leadTime: self::LeadTime,
            processedAt: $mondayThreePm,
            lastBilledAt: null
        ))->getProjectedDeliveryDate($oneWeekBreak);

        $this->assertEquals(new \DateTimeImmutable('21-12-2022 00:00'), $deliveryTime);
    }

    public function testOrderExpectedToArriveOnASundayIsReportedAsMonday()
    {
        $fridayNineAm = new \DateTimeImmutable('09-12-2022 09:00');
        $deliveryTime = (new Calculator(
            customerFrequency: Frequency::weekly,
            leadTime: self::LeadTime,
            processedAt: $fridayNineAm,
            lastBilledAt: null
        ))->getProjectedDeliveryDate();

        $this->assertEquals(new \DateTimeImmutable('12-12-2022 00:00'), $deliveryTime);
    }

    public function testRepeatedOrderOnWeekCadenceWillArriveWhenExpected()
    {
        $mondayNineAm = new \DateTimeImmutable('12-12-2022 09:00:00');
        $deliveryTime = (new Calculator(
            customerFrequency: Frequency::weekly,
            leadTime: self::LeadTime,
            processedAt: $mondayNineAm,
            lastBilledAt: $mondayNineAm
        ))->getProjectedDeliveryDate();

        $this->assertEquals(new \DateTimeImmutable('21-12-2022 00:00'), $deliveryTime);
    }

    public function testOrderOnTwoWeekCadenceWillArriveWhenExpected()
    {
        $mondayNineAm = new \DateTimeImmutable('12-12-2022 09:00:00');
        $deliveryTime = (new Calculator(
            customerFrequency: Frequency::fortnightly,
            leadTime: self::LeadTime,
            processedAt: $mondayNineAm,
            lastBilledAt: $mondayNineAm
        ))->getProjectedDeliveryDate();

        $this->assertEquals(new \DateTimeImmutable('28-12-2022 00:00'), $deliveryTime);
    }

    public function testOrderOnMonthCadenceWillArriveWhenExpected()
    {
        $mondayNineAm = new \DateTimeImmutable('12-12-2022 09:00:00');
        $deliveryTime = (new Calculator(
            customerFrequency: Frequency::monthly,
            leadTime: self::LeadTime,
            processedAt: $mondayNineAm,
            lastBilledAt: $mondayNineAm
        ))->getProjectedDeliveryDate();

        $this->assertEquals(new \DateTimeImmutable('11-01-2023 00:00'), $deliveryTime);
    }

    public function testOneWeekBreakModifiesDeliveryDateAsExpectedOnSubsequentOrder()
    {
        $mondayNineAm = new \DateTimeImmutable('12-12-2022 09:00:00');
        //Wait one extra week, the next will dispatch on 26-12-2022, rather than 19-12-2022
        $oneWeekBreak = new BillingBreak(1);
        $deliveryTime = (new Calculator(
            customerFrequency: Frequency::weekly,
            leadTime: self::LeadTime,
            processedAt: $mondayNineAm,
            lastBilledAt: $mondayNineAm
        ))->getProjectedDeliveryDate($oneWeekBreak);

        $this->assertEquals(new \DateTimeImmutable('28-12-2022 00:00'), $deliveryTime);
    }

    public function testTwoWeekBreakModifiesDeliveryDateAsExpectedOnSubsequentOrder()
    {
        $mondayNineAm = new \DateTimeImmutable('12-12-2022 09:00:00');

        $twoWeekBreak = new BillingBreak(2);
        $deliveryTime = (new Calculator(
            customerFrequency: Frequency::weekly,
            leadTime: self::LeadTime,
            processedAt: $mondayNineAm,
            lastBilledAt: $mondayNineAm
        ))->getProjectedDeliveryDate($twoWeekBreak);

        $this->assertEquals(new \DateTimeImmutable('04-01-2023 00:00'), $deliveryTime);
    }

    public function testFourWeekBreakModifiesDeliveryDateAsExpectedOnSubsequentOrder()
    {
        $mondayNineAm = new \DateTimeImmutable('12-12-2022 09:00:00');

        $fourWeekBreak = new BillingBreak(4);
        $deliveryTime = (new Calculator(
            customerFrequency: Frequency::weekly,
            leadTime: self::LeadTime,
            processedAt: $mondayNineAm,
            lastBilledAt: $mondayNineAm
        ))->getProjectedDeliveryDate($fourWeekBreak);

        $this->assertEquals(new \DateTimeImmutable('18-01-2023 00:00'), $deliveryTime);
    }

    public function testSixWeekBreakModifiesDeliveryDateAsExpectedOnSubsequentOrder()
    {
        $mondayNineAm = new \DateTimeImmutable('12-12-2022 09:00:00');

        $sixWeekBreak = new BillingBreak(6);
        $deliveryTime = (new Calculator(
            customerFrequency: Frequency::weekly,
            leadTime: self::LeadTime,
            processedAt: $mondayNineAm,
            lastBilledAt: $mondayNineAm
        ))->getProjectedDeliveryDate($sixWeekBreak);

        $this->assertEquals(new \DateTimeImmutable('01-02-2023 00:00'), $deliveryTime);
    }
}