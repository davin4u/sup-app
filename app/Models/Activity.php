<?php

namespace App\Models;

use App\Utilities\DataHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    public const ACTIVITY_TYPE_SUP = 1;

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'name',
        'activity_type',
        'training_type',
        'activity_date',
        'gpx_file',
        'distance',
        'duration',
        'avg_speed',
    ];

    /**
     * @return string
     */
    public function formattedDate(): string
    {
        $date = Carbon::parse($this->activity_date);

        return $date->toFormattedDateString() . ' ' . $date->format('H:i:s');
    }

    /**
     * @return string
     */
    public function getActivityTypeName(): string
    {
        return static::getActivityTypes()[$this->activity_type] ?? '-';
    }

    /**
     * @return string
     */
    public function getTrainingTypeName(): string
    {
        return Training::getTrainingTypes()[$this->training_type] ?? '-';
    }

    /**
     * @return string
     */
    public function getHumanDuration(): string
    {
        return DataHelper::secondsToHumanDuration((int) $this->duration);
    }

    /**
     * @return string
     */
    public function getAvgSpeed(): ?float
    {
        return $this->avg_speed;
    }

    /**
     * @return string
     */
    public function getHumanDistance(): string
    {
        return DataHelper::metersToHumanDistance((int) $this->distance);
    }

    /**
     * @return array
     */
    public static function getActivityTypes(): array
    {
        return [
            static::ACTIVITY_TYPE_SUP => __('SUP'),
        ];
    }
}
