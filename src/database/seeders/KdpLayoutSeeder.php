<?php

namespace Database\Seeders;

use App\Models\KdpLayout;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KdpLayoutSeeder extends Seeder
{
    public function run(): void
    {
        if (config('database.default') === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
            KdpLayout::query()->delete();
            DB::statement("DELETE FROM sqlite_sequence WHERE name='kdp_layouts'");
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            KdpLayout::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        if (in_array(config('database.default'), ['pgsql', 'sqlsrv'])) {
            KdpLayout::truncate();
        }


        foreach ($this->getLayoutsFromStaticData() as $layout) {

            KdpLayout::factory()->state([
                'name'               => $layout->name,
                'description'        => $layout->description ?? null,
                'binding_type'       => $layout->binding_type ?? null,
                'page_size'          => $layout->page_size ?? null,
                'width'              => $layout->width ?? null,
                'height'             => $layout->height ?? null,
                'top_margin'         => $layout->top_margin ?? null,
                'bottom_margin'      => $layout->bottom_margin ?? null,
                'outside_margin'     => $layout->outside_margin ?? null,
                'gutter'             => $layout->gutter ?? null,
                'bleed'              => $layout->bleed ?? null,
                'interior_type'      => $layout->interior_type ?? null,
                'paper_color'        => $layout->paper_color ?? null,
                'trim_size'          => $layout->trim_size ?? null,
                'font_settings'      => $layout->font_settings ?? null,
                'minimum_page_count' => $layout->minimum_page_count ?? null,
                'maximum_page_count' => $layout->maximum_page_count ?? null,
            ])->create();

        }
    }


    private function getLayoutsFromStaticData()
    {
        return collect([

            (object) [
                'name'               => '6x9 Paperback - No Bleed',
                'description'        => 'Standard 6 x 9 inch trade paperback interior with no bleed.',
                'binding_type'       => 'Paperback',
                'page_size'          => '6 x 9 in',
                'width'              => 6.00,
                'height'             => 9.00,
                'top_margin'         => 0.75,
                'bottom_margin'      => 0.75,
                'outside_margin'     => 0.75,
                'gutter'             => 0.375,
                'bleed'              => 'No bleed',
                'interior_type'      => 'Black & White',
                'paper_color'        => 'Cream',
                'trim_size'          => '6 x 9',
                'font_settings'      => 'Times New Roman 12pt, 1.15 line spacing',
                'minimum_page_count' => 24,
                'maximum_page_count' => 828,
            ],

            (object) [
                'name'               => '5.5x8.5 Paperback - No Bleed',
                'description'        => 'Compact 5.5 x 8.5 inch paperback with standard margins.',
                'binding_type'       => 'Paperback',
                'page_size'          => '5.5 x 8.5 in',
                'width'              => 5.50,
                'height'             => 8.50,
                'top_margin'         => 0.75,
                'bottom_margin'      => 0.75,
                'outside_margin'     => 0.75,
                'gutter'             => 0.375,
                'bleed'              => 'No bleed',
                'interior_type'      => 'Black & White',
                'paper_color'        => 'White',
                'trim_size'          => '5.5 x 8.5',
                'font_settings'      => 'Garamond 11pt, 1.15 line spacing',
                'minimum_page_count' => 24,
                'maximum_page_count' => 828,
            ],

            (object) [
                'name'               => 'A4 Paperback - No Bleed',
                'description'        => 'Large A4 size paperback suitable for illustrated content.',
                'binding_type'       => 'Paperback',
                'page_size'          => '8.27 x 11.69 in',
                'width'              => 8.27,
                'height'             => 11.69,
                'top_margin'         => 1.00,
                'bottom_margin'      => 1.00,
                'outside_margin'     => 0.90,
                'gutter'             => 0.5,
                'bleed'              => 'No bleed',
                'interior_type'      => 'Color',
                'paper_color'        => 'White',
                'trim_size'          => '8.27 x 11.69',
                'font_settings'      => 'Georgia 12pt, 1.0 line spacing',
                'minimum_page_count' => 24,
                'maximum_page_count' => 828,
            ],

            (object) [
                'name'               => 'KDP Hardcover - No Bleed',
                'description'        => 'Premium dust jacket hardcover layout.',
                'binding_type'       => 'Hardcover',
                'page_size'          => '6 x 9 in',
                'width'              => 6.00,
                'height'             => 9.00,
                'top_margin'         => 0.85,
                'bottom_margin'      => 0.85,
                'outside_margin'     => 0.85,
                'gutter'             => 0.5,
                'bleed'              => 'No bleed',
                'interior_type'      => 'Black & White',
                'paper_color'        => 'Cream',
                'trim_size'          => '6 x 9',
                'font_settings'      => 'Book Antiqua 12pt, 1.0 line spacing',
                'minimum_page_count' => 75,
                'maximum_page_count' => 550,
            ],

            (object) [
                'name'               => '7x10 Paperback - Bleed',
                'description'        => 'Larger 7 x 10 inch paperback with full bleed for color/illustrated books.',
                'binding_type'       => 'Paperback',
                'page_size'          => '7 x 10 in',
                'width'              => 7.00,
                'height'             => 10.00,
                'top_margin'         => 0.75,
                'bottom_margin'      => 0.75,
                'outside_margin'     => 0.75,
                'gutter'             => 0.375,
                'bleed'              => 'Bleed',
                'interior_type'      => 'Premium Color',
                'paper_color'        => 'White',
                'trim_size'          => '7 x 10',
                'font_settings'      => 'Georgia 12pt, 1.15 line spacing',
                'minimum_page_count' => 24,
                'maximum_page_count' => 828,
            ],

        ]);
    }
}
