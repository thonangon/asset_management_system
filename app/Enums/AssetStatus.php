<?php

namespace App\Enums;

enum AssetStatus: string
{
    case AVAILABLE = 'available';
    case ASSIGNED = 'assigned';
    case IN_REPAIR = 'in_repair';
    case RETIRED = 'retired';

    public function getLabel(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Available',
            self::ASSIGNED => 'Assigned',
            self::IN_REPAIR => 'In Repair',
            self::RETIRED => 'Retired',
        };
    }
}