<?php
namespace App\Helpers;

use Illuminate\Support\Collection;
use App\Helpers\SystemHelper;

class NovelHelper
{
    public const STATUS_DRAFT    = 'Draft';
    public const STATUS_ONGOING    = 'Ongoing';
    public const STATUS_PENDING    = 'Pending';
    public const STATUS_FAILED    = 'Failed';
    public const STATUS_STOPPED    = 'Stopped';
    public const STATUS_COMPLETE    = 'Complete';


    public static function statuses(): Collection
    {
        return SystemHelper::toOptions([
            self::STATUS_DRAFT,
            self::STATUS_ONGOING,
            self::STATUS_PENDING,
            self::STATUS_FAILED,
            self::STATUS_STOPPED,
            self::STATUS_COMPLETE,
        ]);
    }

}
