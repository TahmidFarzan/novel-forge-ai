<?php
namespace Database\Seeders;

use App\Models\AiBrainRunner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AiBrainRunnerSeeder extends Seeder
{
    public function run(): void
    {
        if (config('database.default') === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
            AiBrainRunner::query()->delete();
            DB::statement("DELETE FROM sqlite_sequence WHERE name='ai_brains'");
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            AiBrainRunner::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        if (in_array(config('database.default'), ['pgsql', 'sqlsrv'])) {
            AiBrainRunner::truncate();
        }

        foreach ($this->getAiBrainRunnersFromStaticData() as $aiBrainRunner) {
            AiBrainRunner::factory()->state([
                'name'  => $aiBrainRunner->name,
                'brief' => $aiBrainRunner->brief ?? null,
                'url'   => $aiBrainRunner->url,
            ])->create();
        }
    }

    private function getAiBrainRunnersFromStaticData()
    {
        return collect([

            (object) [
                'name'  => 'Olama',
                'brief' => "<strong>Olama</strong> is a lightweight yet powerful language model optimized for efficient inference and fine-tuning. It excels in general-purpose tasks including text generation, code completion, and reasoning.<br><br>📋 <strong>System Requirements:</strong><br>• <strong>CPU:</strong> Intel Core i7 or AMD Ryzen 7 (8+ cores recommended)<br>• <strong>RAM:</strong> 16GB - 32GB minimum<br>• <strong>GPU:</strong> NVIDIA RTX 3060/4060 (8GB VRAM) or higher<br>• <strong>Storage:</strong> Minimum 20GB free space<br>• <strong>OS:</strong> Windows 10/11, Ubuntu 20.04+, or macOS 12+<br>• <strong>CUDA:</strong> CUDA 11.8+ with cuDNN 8.9+ (for GPU acceleration)",
                'url'   => "http://ollama:11434",
            ],

        ]);
    }
}
