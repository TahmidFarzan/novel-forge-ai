<?php
namespace App\Jobs;

use App\Models\NovelGeneratorStep;
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

class DeleteNovelGeneratorStepRelationsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, IsMonitored;

    public int $novelGeneratorStepId;

    public function __construct(int $novelGeneratorStepId)
    {
        $this->novelGeneratorStepId = $novelGeneratorStepId;
    }

    public function uniqueId(): string
    {
        return "delete-novel-generator-{$this->novelGeneratorStepId}-relations";
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
        $novelGeneratorStep = NovelGeneratorStep::find($this->novelGeneratorStepId);

        if ($novelGeneratorStep && ($novelGeneratorStep->activityLogs()->exists())) {

            try {
                DB::transaction(function () use ($novelGeneratorStep) {
                    if ($novelGeneratorStep->activityLogs()->exists()) {
                        $novelGeneratorStep->activityLogs()->delete();
                    }
                });

            } catch (Exception $ex) {
                Log::error("Fail to delete novel generator relations.", [
                    'exception' => $ex,
                ]);

                throw $ex;
            }
        }
    }
}
