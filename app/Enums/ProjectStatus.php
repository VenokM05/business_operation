<?php

namespace App\Enums;

use App\Enums\Concerns\HasMeta;

enum ProjectStatus: string
{
    use HasMeta;

    case Planning = 'planning';
    case Active = 'active';
    case OnHold = 'on_hold';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::OnHold => 'On Hold',
            default => ucfirst($this->value),
        };
    }
}
