<?php
namespace App\Jobs;

use App\Models\NovelGenerator;
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

class DeleteNovelGeneratorRelationsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, IsMonitored;

    public int $novelGeneratorId;

    public function __construct(int $novelGeneratorId)
    {
        $this->novelGeneratorId = $novelGeneratorId;
    }

    public function uniqueId(): string
    {
        return "delete-novel-generator-{$this->novelGeneratorId}-relations";
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
        $novelGenerator = NovelGenerator::find($this->novelGeneratorId);

        if ($novelGenerator && ($novelGenerator->activityLogs()->exists() || $novelGenerator->novelGeneratorSteps()->exists())) {

            try {
                DB::transaction(function () use ($novelGenerator) {
                    if ($novelGenerator->activityLogs()->exists()) {
                        $novelGenerator->activityLogs()->delete();
                    }

                    if ($novelGenerator->novelGeneratorSteps()->exists()) {
                        $novelGenerator->novelGeneratorSteps()->delete();
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
