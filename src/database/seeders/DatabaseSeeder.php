<?php
namespace Database\Seeders;

//use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    //use WithoutModelEvents;

    public function run(): void
    {
        $this->call(UserPermissionSeeder::class);

        $this->call(UserSeeder::class);

        $this->call(GenreSeeder::class);
        $this->call(AudienceSeeder::class);
        $this->call(LanguageSeeder::class);

        $this->call(AiBrainSeeder::class);
        $this->call(KdpLayoutSeeder::class);
        $this->call(DocumentStyleSeeder::class);
        $this->call(AiPromptSeeder::class);
    }
}
