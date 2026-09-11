<?php
namespace App\Jobs;

use App\Models\Novel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class DeleteNovelRelationsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, IsMonitored;

    public int $novelId;

    public function __construct(int $novelId)
    {
        $this->novelId = $novelId;
    }

    public function uniqueId(): string
    {
        return "delete-novel-generator-{$this->novelId}-relations";
    }

    public function retryAfter()
    {
        return 60;
    }

    public function backoff()
    {
        return [61, 123, 185];
    }

    public function handle(): void
    {
        $novel = Novel::find($this->novelId);

        if ($novel && ($novel->activityLogs()->exists() )) {

            try {
                DB::transaction(function () use ($novel) {
                    if ($novel->activityLogs()->exists()) {
                        $novel->activityLogs()->delete();
                    }
                });

            } catch (Exception $ex) {
                Log::error("Fail to delete novel relations.", [
                    'exception' => $ex,
                ]);

                throw $ex;
            }
        }
    }
}
