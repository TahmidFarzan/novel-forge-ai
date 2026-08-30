<?php
namespace Database\Seeders;

use App\Models\AiBrain;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AiBrainSeeder extends Seeder
{
    public function run(): void
    {
        if (config('database.default') === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
            AiBrain::query()->delete();
            DB::statement("DELETE FROM sqlite_sequence WHERE name='ai_brains'");
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            AiBrain::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        if (in_array(config('database.default'), ['pgsql', 'sqlsrv'])) {
            AiBrain::truncate();
        }

        foreach ($this->getAiBrainsFromStaticData() as $aiBrain) {
            AiBrain::factory()->state([
                'name'  => $aiBrain->name,
                'brief' => $aiBrain->brief ?? null,
            ])->create();
        }
    }

    private function getAiBrainsFromStaticData()
    {
        return collect([

            (object) [
                'name'  => 'Qwen 8B',
                'brief' => "<strong>Qwen 8B</strong> is a lightweight yet powerful language model with <strong>8 billion parameters</strong>, optimized for efficient inference and fine-tuning. It excels in general-purpose tasks including text generation, code completion, and reasoning.<br><br>📋 <strong>System Requirements:</strong><br>• <strong>CPU:</strong> Intel Core i7 or AMD Ryzen 7 (8+ cores recommended)<br>• <strong>RAM:</strong> 16GB - 32GB minimum<br>• <strong>GPU:</strong> NVIDIA RTX 3060/4060 (8GB VRAM) or higher<br>• <strong>Storage:</strong> Minimum 20GB free space<br>• <strong>OS:</strong> Windows 10/11, Ubuntu 20.04+, or macOS 12+<br>• <strong>CUDA:</strong> CUDA 11.8+ with cuDNN 8.9+ (for GPU acceleration)",
            ],

            (object) [
                'name'  => 'Qwen 14B',
                'brief' => "<strong>Qwen 14B</strong> is a more advanced language model with <strong>14 billion parameters</strong>, delivering superior performance in complex reasoning, creative writing, and multi-step problem-solving. It offers better contextual understanding and nuanced responses.<br><br>📋 <strong>System Requirements:</strong><br>• <strong>CPU:</strong> Intel Core i9 or AMD Ryzen 9 (12+ cores recommended)<br>• <strong>RAM:</strong> 32GB - 64GB minimum<br>• <strong>GPU:</strong> NVIDIA RTX 4080/4090 (16GB+ VRAM) or NVIDIA A100<br>• <strong>Storage:</strong> Minimum 35GB free space<br>• <strong>OS:</strong> Windows 10/11, Ubuntu 20.04+, or macOS 12+<br>• <strong>CUDA:</strong> CUDA 11.8+ with cuDNN 8.9+ (for GPU acceleration)",
            ],

        ]);
    }
}
