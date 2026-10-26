<?php

namespace App;

enum Month: string
{
    case JANUARY = "january";
    case FEBRUARY = "february";
    case MARCH = "march";
    case APRIL = "april";
    case MAY = "may";
    case JUNE = "june";
    case JULY = "july";
    case AUGUST = "august";
    case SEPTEMBER = "september";
    case OCTOBER = "october";
    case NOVEMBER = "november";
    case DECEMBER = "december";

    public static function byNumber(int $number): self
    {
        return match ($number) {
            1 => self::JANUARY,
            2 => self::FEBRUARY,
            3 => self::MARCH,
            4 => self::APRIL,
            5 => self::MAY,
            6 => self::JUNE,
            7 => self::JULY,
            8 => self::AUGUST,
            9 => self::SEPTEMBER,
            10 => self::OCTOBER,
            11 => self::NOVEMBER,
            12 => self::DECEMBER,
            default => throw new \InvalidArgumentException("Invalid month number: {$number}"),
        };
    }

    /**
     * Get the numeric representation of the month.
     */
    public function number(): int
    {
        return match ($this) {
            self::JANUARY => 1,
            self::FEBRUARY => 2,
            self::MARCH => 3,
            self::APRIL => 4,
            self::MAY => 5,
            self::JUNE => 6,
            self::JULY => 7,
            self::AUGUST => 8,
            self::SEPTEMBER => 9,
            self::OCTOBER => 10,
            self::NOVEMBER => 11,
            self::DECEMBER => 12,
        };
    }
}