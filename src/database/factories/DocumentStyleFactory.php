<?php
namespace Database\Factories;

use App\Helpers\DocumentStyleHelper;
use App\Models\DocumentStyle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentStyle>
 */
class DocumentStyleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sectionName = $this->faker->randomElement([
            DocumentStyleHelper::FRONT_PAGE,
            DocumentStyleHelper::COPYRIGHT,
            DocumentStyleHelper::DEDICATION,
            DocumentStyleHelper::ACKNOWLEDGEMENT,
            DocumentStyleHelper::CONTENTS,
            DocumentStyleHelper::CHAPTER,
            DocumentStyleHelper::CONTENT,
            DocumentStyleHelper::PAGE_NUMBER,
            DocumentStyleHelper::ILLUSTRATOR,
        ]);

        return [
            'section_name' => $sectionName,
            'settings'     => $this->settingsFor($sectionName),
        ];
    }

    private function settingsFor(string $sectionName): array
    {
        $settings = [
            DocumentStyleHelper::FRONT_PAGE => [
                DocumentStyleHelper::TITLE_FONT_SIZE      => 28,
                DocumentStyleHelper::SUBTITLE_FONT_SIZE   => 16,
                DocumentStyleHelper::AUTHOR_FONT_SIZE     => 12,
                DocumentStyleHelper::HORIZONTAL_ALIGNMENT => DocumentStyleHelper::ALIGN_CENTER,
                DocumentStyleHelper::VERTICAL_ALIGNMENT   => DocumentStyleHelper::ALIGN_CENTER,
            ],

            DocumentStyleHelper::COPYRIGHT => [
                DocumentStyleHelper::FONT_SIZE            => 10,
                DocumentStyleHelper::FONT_STYLE           => 'normal',
                DocumentStyleHelper::HORIZONTAL_ALIGNMENT => DocumentStyleHelper::ALIGN_CENTER,
                DocumentStyleHelper::VERTICAL_ALIGNMENT   => DocumentStyleHelper::ALIGN_CENTER,
            ],

            DocumentStyleHelper::DEDICATION => [
                DocumentStyleHelper::FONT_SIZE            => 12,
                DocumentStyleHelper::FONT_STYLE           => 'italic',
                DocumentStyleHelper::HORIZONTAL_ALIGNMENT => DocumentStyleHelper::ALIGN_CENTER,
                DocumentStyleHelper::VERTICAL_ALIGNMENT   => DocumentStyleHelper::ALIGN_CENTER,
            ],

            DocumentStyleHelper::ACKNOWLEDGEMENT => [
                DocumentStyleHelper::FONT_SIZE            => 12,
                DocumentStyleHelper::FONT_STYLE           => 'normal',
                DocumentStyleHelper::HORIZONTAL_ALIGNMENT => DocumentStyleHelper::ALIGN_CENTER,
                DocumentStyleHelper::VERTICAL_ALIGNMENT   => DocumentStyleHelper::ALIGN_CENTER,
            ],

            DocumentStyleHelper::CONTENTS => [
                DocumentStyleHelper::CONTENTS_TITLE_FONT_SIZE => 18,
                DocumentStyleHelper::ITEM_FONT_SIZE           => 11,
                DocumentStyleHelper::HORIZONTAL_ALIGNMENT     => DocumentStyleHelper::ALIGN_CENTER,
            ],

            DocumentStyleHelper::CHAPTER => [
                DocumentStyleHelper::CHAPTER_NUMBER_FONT_SIZE => 14,
                DocumentStyleHelper::CHAPTER_TITLE_FONT_SIZE  => 24,
                DocumentStyleHelper::CHAPTER_TITLE_STYLE      => 'bold',
                DocumentStyleHelper::HORIZONTAL_ALIGNMENT     => DocumentStyleHelper::ALIGN_CENTER,
                DocumentStyleHelper::VERTICAL_ALIGNMENT       => DocumentStyleHelper::ALIGN_CENTER,
                DocumentStyleHelper::CHAPTER_STANDALONE_PAGE  => true,
            ],

            DocumentStyleHelper::CONTENT => [
                DocumentStyleHelper::FONT_FAMILY          => 'Garamond',
                DocumentStyleHelper::FONT_SIZE            => 11,
                DocumentStyleHelper::FONT_STYLE           => 'normal',
                DocumentStyleHelper::HORIZONTAL_ALIGNMENT => DocumentStyleHelper::ALIGN_JUSTIFY,
                DocumentStyleHelper::LINE_SPACING         => 1.15,
                DocumentStyleHelper::FIRST_LINE_INDENT    => 0.25,
                DocumentStyleHelper::PARAGRAPH_SPACING    => 6,
            ],

            DocumentStyleHelper::PAGE_NUMBER => [
                DocumentStyleHelper::FONT_FAMILY => 'Garamond',
                DocumentStyleHelper::FONT_SIZE   => 10,
                DocumentStyleHelper::POSITION    => DocumentStyleHelper::POSITION_BOTTOM_CENTER,
                DocumentStyleHelper::START_FROM  => DocumentStyleHelper::START_FROM_CHAPTER,
            ],

            DocumentStyleHelper::ILLUSTRATOR => [
                DocumentStyleHelper::ENABLED              => false,
                DocumentStyleHelper::POSITION             => 'center',
                DocumentStyleHelper::HORIZONTAL_ALIGNMENT => DocumentStyleHelper::ALIGN_CENTER,
                DocumentStyleHelper::VERTICAL_ALIGNMENT   => DocumentStyleHelper::ALIGN_CENTER,
            ],
        ];

        return $settings[$sectionName] ?? [];
    }
}
