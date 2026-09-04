<?php

namespace Database\Seeders;

use App\Models\AiPrompt;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AiPromptSeeder extends Seeder
{
    public function run(): void
    {
        if (config('database.default') === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
            AiPrompt::query()->delete();
            DB::statement("DELETE FROM sqlite_sequence WHERE name='ai_prompts'");
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            AiPrompt::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        if (in_array(config('database.default'), ['pgsql', 'sqlsrv'])) {
            AiPrompt::truncate();
        }


        foreach ($this->getAiPromptsFromStaticData() as $aiPrompt) {

            AiPrompt::factory()->state([
                'name'   => $aiPrompt->name,
                'prompt' => $aiPrompt->prompt,
            ])->create();

        }
    }


    private function getAiPromptsFromStaticData()
    {
        return collect([

            (object) [
                'name' => 'Planning',
                'prompt' => <<<'PROMPT'
You are a professional Novel Generation Planning AI.

Your task is NOT to write the novel.

Your task is to analyze the user's novel requirements and create a complete generation workflow plan that another AI system will follow to generate the novel step by step.

Analyze the provided novel information carefully and decide:

1. What type of novel should be generated.
2. What creative elements are required.
3. What analysis and generation steps are necessary.
4. The correct sequence of those steps.
5. Dependencies between steps.
6. What input each step requires.
7. What output each step should produce.

Do not create unnecessary steps.
Do not use fixed workflows.
The workflow must be dynamically created based on the novel requirements.

---

USER NOVEL INFORMATION:

Novel Title:
{{novel_title}}

Main Genre:
{{main_genre}}

Sub Genres:
{{sub_genres}}

Main Character Gender:
{{character_gender}}

Is 18+:
{{is_18_plus}}

Mature Sexual Content:
{{mature_content}}

Language:
{{language}}

Novel Continuity:
{{continuity}}

Additional Novel Information:
{{additional_information}}

Illustrator Type:
{{illustrator_type}}

---

Now analyze this novel and create a generation workflow.

The workflow must include:

* Total number of required steps
* Step sequence
* Step name
* Step purpose
* Required input
* Expected output
* Dependency on previous steps

Return ONLY valid JSON.

Required JSON structure:

{
"novel_analysis": {
"novel_type": "",
"complexity_level": "",
"estimated_steps": 0
},

"workflow": [
{
"step_number": 1,
"step_name": "",
"purpose": "",
"depends_on": [],
"required_input": [],
"expected_output": []
}
]
}

Rules:

1. The first step must always be:
   "Novel Generation Planning"

2. After planning, create only the steps required for this specific novel.

3. Consider genre requirements:

* Fantasy may require world rules, power systems, magic systems.
* Sci-fi may require technology, science rules, universe design.
* Romance may require relationship development.
* Mystery may require investigation structure and clue planning.
* Historical fiction may require historical research.

4. Chapter Planning must depend on all required story foundation steps.

5. Chapter Writing must only happen after Chapter Planning is completed.

6. The output must be suitable for storing inside a database workflow engine.
PROMPT,
            ],

        ]);
    }
}
