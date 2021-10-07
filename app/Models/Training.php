<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory;

    public const TRAINING_TYPE_INTERVAL = 1;
    public const TRAINING_TYPE_LONG_DISTANCE = 2;

    /**
     * @return array
     */
    public static function getTrainingTypes(): array
    {
        return [
            static::TRAINING_TYPE_INTERVAL => __('Interval training'),
            static::TRAINING_TYPE_LONG_DISTANCE => __('Long distance'),
        ];
    }
}
