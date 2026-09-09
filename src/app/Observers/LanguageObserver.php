<?php
namespace App\Observers;


use App\Models\Language;
use Illuminate\Support\Str;
use App\Jobs\DeleteLanguageRelationsJob;

class LanguageObserver
{
    public function deleting(Language $language): void
    {
        DeleteLanguageRelationsJob::dispatchSync($language->id);
    }
}
