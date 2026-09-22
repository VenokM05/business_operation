<?php

namespace App\Enums;

use App\Enums\Concerns\HasMeta;

/**
 * Service request lifecycle (PRD Section 7/8).
 *
 * Legal forward flow:
 *   NEW -> ASSIGNED -> IN PROGRESS -> PENDING -> RESOLVED -> CLOSED
 * with IN PROGRESS -> RESOLVED allowed and PENDING -> IN PROGRESS for rework,
 * plus CANCELLED reachable from any non-terminal state.
 */
enum RequestStatus: string
{
    use HasMeta;

    case New = 'new';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case Pending = 'pending';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::InProgress => 'In Progress',
            self::New => 'New',
            default => ucfirst($this->value),
        };
    }

    /**
     * Allowed transitions keyed by the current status.
     *
     * @return array<string, array<int, self>>
     */
    public static function transitionMap(): array
    {
        return [
            self::New->value => [self::Assigned, self::Cancelled],
            self::Assigned->value => [self::InProgress, self::Cancelled],
            self::InProgress->value => [self::Pending, self::Resolved, self::Cancelled],
            self::Pending->value => [self::InProgress, self::Cancelled],
            self::Resolved->value => [self::Closed],
            self::Closed->value => [],
            self::Cancelled->value => [],
        ];
    }

    /**
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return self::transitionMap()[$this->value] ?? [];
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function isTerminal(): bool
    {
        return $this->allowedTransitions() === [];
    }
}
