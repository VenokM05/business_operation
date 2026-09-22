<?php

namespace App\Enums\Concerns;

/**
 * Provides value => label option maps and raw value lists for string-backed enums.
 * The consuming enum must define a label(): string method.
 */
trait HasMeta
{
    /**
     * @return array<string, string> value => human label
     */
    public static function options(): array
    {
        $out = [];
        foreach (self::cases() as $case) {
            $out[$case->value] = $case->label();
        }

        return $out;
    }

    /**
     * @return array<int, string> list of raw values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
