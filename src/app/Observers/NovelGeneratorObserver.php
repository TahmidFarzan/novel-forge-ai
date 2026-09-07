<?php
namespace App\Observers;


use App\Models\NovelGenerator;
use Illuminate\Support\Str;
use App\Jobs\DeleteNovelGeneratorRelationsJob;

class NovelGeneratorObserver
{
    public function deleting(NovelGenerator $novelGenerator): void
    {
        DeleteNovelGeneratorRelationsJob::dispatchSync($novelGenerator->id);
    }
}
