<?php

namespace App\Models;

use App\Policies\NovelGeneratorStepPolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

#[Table('novel_generator_steps')]
#[Fillable([
    'name',
    'slug',
    'depend_on_step_ids',
    'previous_step_id',
    'next_step_id',
    'ai_prompt_id',
    'created_by_id',
])]
#[UsePolicy(NovelGeneratorStepPolicy::class)]
class NovelGeneratorStep extends Model
{
    use HasFactory, HasSlug, LogsActivity;

    protected $appends = [];

    protected function casts(): array
    {
        return [
            'depend_on_step_ids' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'slug',
                'depend_on_step_ids',
                'previous_step_id',
                'next_step_id',
                'ai_prompt_id',
            ])
            ->useLogName('Novel Generator Step')
            ->setDescriptionForEvent(fn (string $eventName) => "The record has been {$eventName}.")
            ->logOnlyDirty()
            ->logExcept([
                'id',
                'created_by_id',
                'created_at',
            ])
            ->dontLogEmptyChanges();
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->saveSlugsTo('slug')
            ->generateSlugsFrom('name')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(255)
            ->usingSuffixGenerator(fn () => Str::lower(Str::random(5)));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function aiPrompt(): BelongsTo
    {
        return $this->belongsTo(AiPrompt::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function dependencySteps()
    {
        return NovelGeneratorStep::query()
            ->whereIn('id', $this->depend_on_step_ids ?? [])
            ->orderBy('id');
    }

    public function dependencyStepIds(): array
    {
        return array_values(array_unique(array_map('intval', $this->depend_on_step_ids ?? [])));
    }

    public function latestActivityLog(): MorphOne
    {
        return $this->morphOne(Activity::class, 'subject')->latestOfMany();
    }

    public function nextStep(): BelongsTo
    {
        return $this->belongsTo(self::class, 'next_step_id');
    }

    public function previousStep(): BelongsTo
    {
        return $this->belongsTo(self::class, 'previous_step_id');
    }
}
