<?php
namespace App\Jobs;

use App\Models\NovelType;
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

class DeleteNovelTypeRelationsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, IsMonitored;

    public int $novelTypeId;

    public function __construct(int $novelTypeId)
    {
        $this->novelTypeId = $novelTypeId;
    }

    public function uniqueId(): string
    {
        return "delete-novel-type-{$this->novelTypeId}-relations";
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
        $novelType = NovelType::find($this->novelTypeId);

        if ($novelType && $novelType->activityLogs()->exists()) {

            try {

                DB::transaction(function () use ($novelType) {
                    if ($novelType->activityLogs()->exists()) {
                        $novelType->activityLogs()->delete();
                    }
                });

            } catch (Exception $ex) {
                Log::error("Fail to delete novel type relations.", [
                    'exception' => $ex,
                ]);

                throw $ex;
            }
        }
    }
}