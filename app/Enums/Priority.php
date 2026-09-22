<?php

namespace App\Enums;

use App\Enums\Concerns\HasMeta;

enum Priority: string
{
    use HasMeta;

    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
