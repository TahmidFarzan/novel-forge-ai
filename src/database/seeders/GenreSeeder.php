<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GenreSeeder extends Seeder
{
    public function run(): void
    {
        if (config('database.default') === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
            Genre::query()->delete();
            DB::statement("DELETE FROM sqlite_sequence WHERE name='genres'");
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Genre::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        if (in_array(config('database.default'), ['pgsql', 'sqlsrv'])) {
            Genre::truncate();
        }


        foreach ($this->getGenresFromStaticData() as $genre) {

            Genre::factory()->state([
                'name'      => $genre->name,
                'brief'     => $genre->brief ?? null,
            ])->create();

        }
    }


    private function getGenresFromStaticData()
    {
        return collect([

            // Fiction Genres

            (object) [
                'name' => 'Fantasy',
                'brief' => 'Fantasy fiction with imaginary worlds and magic',
            ],

            (object) [
                'name' => 'Dark Fantasy',
                'brief' => 'Dark and mysterious fantasy stories',
            ],

            (object) [
                'name' => 'Historical Fiction',
                'brief' => 'Stories based on historical events and periods',
            ],

            (object) [
                'name' => 'Family Drama',
                'brief' => 'Stories about family relationships and emotions',
            ],

            (object) [
                'name' => 'Romance',
                'brief' => 'Love and relationship based stories',
            ],

            (object) [
                'name' => 'Mystery',
                'brief' => 'Mystery and investigation stories',
            ],

            (object) [
                'name' => 'Thriller',
                'brief' => 'Suspenseful and exciting stories',
            ],

            (object) [
                'name' => 'Horror',
                'brief' => 'Scary and horror fiction stories',
            ],

            (object) [
                'name' => 'Science Fiction',
                'brief' => 'Science based futuristic fiction',
            ],

            (object) [
                'name' => 'Adventure',
                'brief' => 'Journey and exploration based stories',
            ],


            // Non Fiction Genres

            (object) [
                'name' => 'Biography',
                'brief' => 'Life stories of real people',
            ],

            (object) [
                'name' => 'Autobiography',
                'brief' => 'Life story written by the person themselves',
            ],

            (object) [
                'name' => 'History',
                'brief' => 'Historical books and events',
            ],

            (object) [
                'name' => 'Philosophy',
                'brief' => 'Books about ideas and philosophy',
            ],

            (object) [
                'name' => 'Self Help',
                'brief' => 'Personal development and improvement books',
            ],


            // Special Genres

            (object) [
                'name' => 'Psychological',
                'brief' => 'Stories focused on human mind and emotions',
            ],

            (object) [
                'name' => 'Supernatural',
                'brief' => 'Stories involving supernatural elements',
            ],

            (object) [
                'name' => 'Crime',
                'brief' => 'Crime related fiction stories',
            ],

            (object) [
                'name' => 'Political Fiction',
                'brief' => 'Stories involving politics and society',
            ],

            (object) [
                'name' => 'War',
                'brief' => 'Stories based on wars and conflicts',
            ],

            (object) [
                'name' => 'Young Adult',
                'brief' => 'Stories for young adult readers',
            ],

            (object) [
                'name' => 'Children',
                'brief' => 'Books for children',
            ],

            (object) [
                'name' => 'Poetry',
                'brief' => 'Poetry and verses',
            ],

            (object) [
                'name' => 'Short Story',
                'brief' => 'Short fiction stories',
            ],

        ]);
    }
}
