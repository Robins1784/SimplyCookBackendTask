<?php

namespace Domain\Delivery;

class BillingBreak
{

    // In a full scale application this could be configurable so as not to require a code change to modify
    private const MinLength = 1;
    private const MaxLength = 6;
    private int $lengthInWeeks;

    public function __construct(int $lengthInWeeks)
    {
        $this->validate($lengthInWeeks);

        $this->lengthInWeeks = $lengthInWeeks;
    }

    public function getLengthInWeeks() {
        return $this->lengthInWeeks;
    }

    public function getLengthInDays() {
        return $this->lengthInWeeks * 7;
    }

    private function validate(int $lengthInWeeks): void {
        if ($lengthInWeeks < self::MinLength || $lengthInWeeks > self::MaxLength) {
            throw new \Exception(
                sprintf(
                    'Invalid break length, must be greater than % and less than %s',
                    self::MinLength,
                    self::MaxLength
                )
            );
        }
    }
}