<?php

namespace Database\Seeders;

use App\Models\Audience;
use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GenreAudienceSeeder extends Seeder
{
    public function run(): void
    {
        if (config('database.default') === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
            DB::table('genre_audience')->delete();
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('genre_audience')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        if (in_array(config('database.default'), ['pgsql', 'sqlsrv'])) {
            DB::table('genre_audience')->truncate();
        }

        $mapping = [

            'fantasy' => [
                'children',
                'young-adult',
                'adult',
            ],

            'dark-fantasy' => [
                'adult',
            ],

            'historical-fiction' => [
                'children',
                'young-adult',
                'adult',
            ],

            'family-drama' => [
                'children',
                'young-adult',
                'adult',
            ],

            'romance' => [
                'young-adult',
                'adult',
            ],

            'mystery' => [
                'children',
                'young-adult',
                'adult',
            ],

            'thriller' => [
                'young-adult',
                'adult',
            ],

            'horror' => [
                'young-adult',
                'adult',
            ],

            'science-fiction' => [
                'children',
                'young-adult',
                'adult',
            ],

            'adventure' => [
                'children',
                'young-adult',
                'adult',
            ],

            'biography' => [
                'children',
                'young-adult',
                'adult',
            ],

            'autobiography' => [
                'young-adult',
                'adult',
            ],

            'history' => [
                'children',
                'young-adult',
                'adult',
            ],

            'philosophy' => [
                'young-adult',
                'adult',
            ],

            'self-help' => [
                'children',
                'young-adult',
                'adult',
            ],

            'psychological' => [
                'young-adult',
                'adult',
            ],

            'supernatural' => [
                'children',
                'young-adult',
                'adult',
            ],

            'crime' => [
                'young-adult',
                'adult',
            ],

            'political-fiction' => [
                'young-adult',
                'adult',
            ],

            'war' => [
                'young-adult',
                'adult',
            ],

            'young-adult' => [
                'young-adult',
            ],

            'children' => [
                'children',
            ],

            'poetry' => [
                'children',
                'young-adult',
                'adult',
            ],

            'short-story' => [
                'children',
                'young-adult',
                'adult',
            ],

            'epic-fantasy' => [
                'young-adult',
                'adult',
            ],

            'urban-fantasy' => [
                'young-adult',
                'adult',
            ],

            'historical-romance' => [
                'adult',
            ],

            'dystopian' => [
                'young-adult',
                'adult',
            ],

            'post-apocalyptic' => [
                'young-adult',
                'adult',
            ],

            'steampunk' => [
                'children',
                'young-adult',
                'adult',
            ],

            'cyberpunk' => [
                'young-adult',
                'adult',
            ],

            'detective-fiction' => [
                'children',
                'young-adult',
                'adult',
            ],

            'legal-drama' => [
                'young-adult',
                'adult',
            ],

            'medical-drama' => [
                'young-adult',
                'adult',
            ],

            'coming-of-age' => [
                'young-adult',
            ],

            'western' => [
                'children',
                'young-adult',
                'adult',
            ],

            'mythology' => [
                'children',
                'young-adult',
                'adult',
            ],

            'satire' => [
                'young-adult',
                'adult',
            ],

            'erotic-romance' => [
                'adult',
            ],
        ];

        foreach ($mapping as $genreSlug => $audienceSlugs) {

            $genre = Genre::where('slug', $genreSlug)->first();

            if (! $genre) {
                continue;
            }

            $audienceIds = Audience::whereIn('slug', $audienceSlugs)
                ->pluck('id')
                ->toArray();

            $genre->audiences()->sync($audienceIds);
        }
    }
}