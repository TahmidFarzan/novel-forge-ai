<?php
namespace App\Observers;

use App\Models\Audience;
use App\Jobs\DeleteAudienceRelationsJob;

class AudienceObserver
{
    public function deleting(Audience $audience): void
    {
        DeleteAudienceRelationsJob::dispatchSync($audience->id);
    }
}
