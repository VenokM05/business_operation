<?php

namespace App\Enums;

use App\Enums\Concerns\HasMeta;

enum RequestCategory: string
{
    use HasMeta;

    case Website = 'website';
    case Software = 'software';
    case TechnicalSupport = 'technical_support';
    case Content = 'content';
    case SystemMaintenance = 'system_maintenance';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::TechnicalSupport => 'Technical Support',
            self::SystemMaintenance => 'System Maintenance',
            default => ucfirst($this->value),
        };
    }
}
