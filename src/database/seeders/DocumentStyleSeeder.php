<?php

namespace Database\Seeders;

use App\Helpers\DocumentStyleHelper;
use App\Models\DocumentStyle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentStyleSeeder extends Seeder
{
    public function run(): void
    {
        if (config('database.default') === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
            DocumentStyle::query()->delete();
            DB::statement("DELETE FROM sqlite_sequence WHERE name='document_styles'");
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DocumentStyle::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        if (in_array(config('database.default'), ['pgsql', 'sqlsrv'])) {
            DocumentStyle::truncate();
        }

        foreach ($this->getDocumentStylesFromStaticData() as $documentStyle) {
            DocumentStyle::create([
                'section_name' => $documentStyle->section_name,
                'settings'     => $documentStyle->settings,
            ]);
        }
    }


    private function getDocumentStylesFromStaticData()
    {
        return collect([

            (object) [
                'section_name' => DocumentStyleHelper::FRONT_PAGE,
                'settings'     => [
                    DocumentStyleHelper::TITLE_FONT_SIZE       => 28,
                    DocumentStyleHelper::SUBTITLE_FONT_SIZE    => 16,
                    DocumentStyleHelper::AUTHOR_FONT_SIZE      => 12,
                    DocumentStyleHelper::HORIZONTAL_ALIGNMENT  => DocumentStyleHelper::ALIGN_CENTER,
                    DocumentStyleHelper::VERTICAL_ALIGNMENT    => DocumentStyleHelper::ALIGN_CENTER,
                ],
            ],

            (object) [
                'section_name' => DocumentStyleHelper::COPYRIGHT,
                'settings'     => [
                    DocumentStyleHelper::FONT_SIZE             => 10,
                    DocumentStyleHelper::FONT_STYLE            => 'normal',
                    DocumentStyleHelper::HORIZONTAL_ALIGNMENT  => DocumentStyleHelper::ALIGN_CENTER,
                    DocumentStyleHelper::VERTICAL_ALIGNMENT    => DocumentStyleHelper::ALIGN_CENTER,
                ],
            ],

            (object) [
                'section_name' => DocumentStyleHelper::DEDICATION,
                'settings'     => [
                    DocumentStyleHelper::FONT_SIZE             => 12,
                    DocumentStyleHelper::FONT_STYLE            => 'italic',
                    DocumentStyleHelper::HORIZONTAL_ALIGNMENT  => DocumentStyleHelper::ALIGN_CENTER,
                    DocumentStyleHelper::VERTICAL_ALIGNMENT    => DocumentStyleHelper::ALIGN_CENTER,
                ],
            ],

            (object) [
                'section_name' => DocumentStyleHelper::ACKNOWLEDGEMENT,
                'settings'     => [
                    DocumentStyleHelper::FONT_SIZE             => 12,
                    DocumentStyleHelper::FONT_STYLE            => 'normal',
                    DocumentStyleHelper::HORIZONTAL_ALIGNMENT  => DocumentStyleHelper::ALIGN_CENTER,
                    DocumentStyleHelper::VERTICAL_ALIGNMENT    => DocumentStyleHelper::ALIGN_CENTER,
                ],
            ],

            (object) [
                'section_name' => DocumentStyleHelper::CONTENTS,
                'settings'     => [
                    DocumentStyleHelper::CONTENTS_TITLE_FONT_SIZE => 18,
                    DocumentStyleHelper::ITEM_FONT_SIZE           => 11,
                    DocumentStyleHelper::HORIZONTAL_ALIGNMENT     => DocumentStyleHelper::ALIGN_CENTER,
                ],
            ],

            (object) [
                'section_name' => DocumentStyleHelper::CHAPTER,
                'settings'     => [
                    DocumentStyleHelper::CHAPTER_NUMBER_FONT_SIZE => 14,
                    DocumentStyleHelper::CHAPTER_TITLE_FONT_SIZE  => 24,
                    DocumentStyleHelper::CHAPTER_TITLE_STYLE      => 'bold',
                    DocumentStyleHelper::HORIZONTAL_ALIGNMENT     => DocumentStyleHelper::ALIGN_CENTER,
                    DocumentStyleHelper::VERTICAL_ALIGNMENT       => DocumentStyleHelper::ALIGN_CENTER,
                    DocumentStyleHelper::CHAPTER_STANDALONE_PAGE  => true,
                ],
            ],

            (object) [
                'section_name' => DocumentStyleHelper::CONTENT,
                'settings'     => [
                    DocumentStyleHelper::FONT_FAMILY          => 'Garamond',
                    DocumentStyleHelper::FONT_SIZE            => 11,
                    DocumentStyleHelper::FONT_STYLE           => 'normal',
                    DocumentStyleHelper::HORIZONTAL_ALIGNMENT => DocumentStyleHelper::ALIGN_JUSTIFY,
                    DocumentStyleHelper::LINE_SPACING         => 1.15,
                    DocumentStyleHelper::FIRST_LINE_INDENT    => 0.25,
                    DocumentStyleHelper::PARAGRAPH_SPACING    => 6,
                ],
            ],

            (object) [
                'section_name' => DocumentStyleHelper::PAGE_NUMBER,
                'settings'     => [
                    DocumentStyleHelper::FONT_FAMILY => 'Garamond',
                    DocumentStyleHelper::FONT_SIZE   => 10,
                    DocumentStyleHelper::POSITION    => DocumentStyleHelper::POSITION_BOTTOM_CENTER,
                    DocumentStyleHelper::START_FROM  => DocumentStyleHelper::START_FROM_CHAPTER,
                ],
            ],

            (object) [
                'section_name' => DocumentStyleHelper::ILLUSTRATOR,
                'settings'     => [
                    DocumentStyleHelper::ENABLED              => false,
                    DocumentStyleHelper::POSITION             => 'center',
                    DocumentStyleHelper::HORIZONTAL_ALIGNMENT => DocumentStyleHelper::ALIGN_CENTER,
                    DocumentStyleHelper::VERTICAL_ALIGNMENT   => DocumentStyleHelper::ALIGN_CENTER,
                ],
            ],

        ]);
    }
}
