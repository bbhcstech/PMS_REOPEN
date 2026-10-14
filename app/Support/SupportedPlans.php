<?php

namespace App\Support;

class SupportedPlans
{
    public const SLUGS = ['free', 'gold', 'platinum', 'diamond'];

    public static function defaults(): array
    {
        return [
            'free' => ['name' => 'FREE', 'monthly_price' => 0, 'yearly_price' => 0, 'max_users' => 5, 'max_storage_mb' => 5120],
            'gold' => ['name' => 'GOLD', 'monthly_price' => 4999, 'yearly_price' => 49990, 'max_users' => 25, 'max_storage_mb' => 25600],
            'platinum' => ['name' => 'PLATINUM', 'monthly_price' => 9999, 'yearly_price' => 99990, 'max_users' => 100, 'max_storage_mb' => 102400],
            'diamond' => ['name' => 'DIAMOND', 'monthly_price' => 19999, 'yearly_price' => 199990, 'max_users' => 0, 'max_storage_mb' => 512000],
        ];
    }
}
