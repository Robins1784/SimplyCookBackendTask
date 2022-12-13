<?php

declare(strict_types=1);

namespace Domain\Delivery;

use DateInterval;
use DateTimeImmutable;

class DateCalculator
{
    // Hardcoding this for brevity, but it would ideally be injected, configurable without code change, and not locked to a single value.
    private const UndeliverableDay = 'sun';

    // Favouring DateTimeImmutable here to prevent side effects of date manipulation
    public function __construct(
        private readonly Frequency $customerFrequency,
        private readonly int $leadTime,
        private readonly DateTimeImmutable $processedAt,
        private readonly ?DateTimeImmutable $lastBilledAt = null,
    ) {
    }

    public function getProjectedDeliveryDate(?BillingBreak $break = null): DateTimeImmutable
    {
        $dispatchDate = $this->getDispatchDate($break);
        return $this->calculateEarliestDeliveryDate($dispatchDate);
    }


    // Assuming that dispatch can happen on any day of the week, and not taking into account other potential shutdowns
    // Also assuming that the 'default' dispatch time is the customers billing cadence away from their last complete billing
    private function getDispatchDate(?BillingBreak $break = null): DateTimeImmutable
    {
        $breakInDays = $break?->getLengthInDays() ?? 0;

        if (!$this->lastBilledAt) {
            return $this->getDispatchDateForFirstOrder($breakInDays);
        }

        return $this->getDispatchDateForFollowUpOrder($breakInDays);
    }

    private function getDispatchDateForFirstOrder(int $breakInDays): DateTimeImmutable
    {
        $dispatch = $this->processedAt;

        //If the customer is applying a break, the cutoff does not apply, as we would not be dispatching same day
        if ($breakInDays > 0) {
            $dispatch = $dispatch->add(new DateInterval(sprintf('P%sD', $breakInDays)));
        } elseif ($this->isAfterDispatchCutoff()) {
            $dispatch = $dispatch->add(new DateInterval('P1D'));
        }

        return $dispatch->setTime(0, 0);
    }

    private function getDispatchDateForFollowUpOrder(int $breakInDays): DateTimeImmutable
    {
        $nextDateInCadence = $this->lastBilledAt->add(
            new DateInterval(sprintf('P%sD', $this->customerFrequency->value))
        );

        return $nextDateInCadence->add(new DateInterval(sprintf('P%sD', $breakInDays)))->setTime(0, 0);
    }


    private function isAfterDispatchCutoff(): bool
    {
        $cutoff = $this->processedAt->setTime(14, 0);
        return $cutoff <= $this->processedAt;
    }

    private function calculateEarliestDeliveryDate(DateTimeImmutable $dispatchDate): DateTimeImmutable
    {
        $deliveryDate = $dispatchDate->add(new DateInterval(sprintf('P%sD', $this->leadTime)));
        if (!$this->isValidDeliveryDay($deliveryDate)) {
            $deliveryDate = $deliveryDate->add(new DateInterval('P1D'));
        }
        return $deliveryDate;
    }

    // This only takes into account Sundays, and assumes the lead time will be extended
    // A production version would need to take public holidays into account at the very least
    private function isValidDeliveryDay(DateTimeImmutable $projectedDelivery): bool
    {
        return strtolower($projectedDelivery->format('D')) !== self::UndeliverableDay;
    }
}
