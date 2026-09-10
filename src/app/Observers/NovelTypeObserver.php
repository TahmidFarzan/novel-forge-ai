<?php
namespace App\Observers;


use App\Models\NovelType;
use Illuminate\Support\Str;
use App\Jobs\DeleteNovelTypeRelationsJob;

class NovelTypeObserver
{
    public function deleting(NovelType $novelType): void
    {
        DeleteNovelTypeRelationsJob::dispatchSync($novelType->id);
    }
}