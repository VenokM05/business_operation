<?php

namespace App\Enums;

use App\Enums\Concerns\HasMeta;

enum ClientStatus: string
{
    use HasMeta;

    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
