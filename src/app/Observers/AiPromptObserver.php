<?php
namespace App\Observers;

use App\Models\AiPrompt;
use Illuminate\Support\Str;

class AiPromptObserver
{
    public function creating(AiPrompt $aiPrompt): void
    {
        $aiPrompt->code = Str::studly($aiPrompt->name);
    }
}
