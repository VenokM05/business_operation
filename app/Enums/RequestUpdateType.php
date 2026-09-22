<?php

namespace App\Enums;

use App\Enums\Concerns\HasMeta;

enum RequestUpdateType: string
{
    use HasMeta;

    case Comment = 'comment';
    case StatusChange = 'status_change';
    case Assignment = 'assignment';

    public function label(): string
    {
        return match ($this) {
            self::StatusChange => 'Status Change',
            self::Assignment => 'Assignment',
            default => ucfirst($this->value),
        };
    }
}
