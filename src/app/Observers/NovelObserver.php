<?php
namespace App\Observers;


use App\Models\Novel;
use Illuminate\Support\Str;
use App\Jobs\DeleteNovelRelationsJob;

class NovelObserver
{
    public function deleting(Novel $novel): void
    {
        DeleteNovelRelationsJob::dispatchSync($novel->id);
    }
}
