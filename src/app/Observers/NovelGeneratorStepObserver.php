<?php
namespace App\Observers;


use App\Models\NovelGeneratorStep;
use Illuminate\Support\Str;
use App\Jobs\DeleteNovelGeneratorStepRelationsJob;

class NovelGeneratorStepObserver
{
    public function deleting(NovelGeneratorStep $novelGeneratorStep): void
    {
        DeleteNovelGeneratorStepRelationsJob::dispatchSync($novelGeneratorStep->id);
    }
}
