<?php

namespace App\Enums;

use App\Enums\Concerns\HasMeta;

enum TaskStatus: string
{
    use HasMeta;

    case ToDo = 'to_do';
    case InProgress = 'in_progress';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::ToDo => 'To Do',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
        };
    }
}
