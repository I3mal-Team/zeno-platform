<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * `seasonal_work` rather than `season`, which would collide with the work type
 * of the same name.
 */
final class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['restaurants',   'مطاعم ومقاهي',   'coffee',        'amber'],
            ['cleaning',      'نظافة وخدمات',   'broom',         'green'],
            ['logistics',     'مستودعات ونقل',  'truck',         'red'],
            ['events',        'فعاليات',        'magic-star',    'purple'],
            ['retail',        'مبيعات وتجزئة',  'shop',          'blue'],
            ['security',      'أمن وسلامة',     'shield',        'teal'],
            ['operations',    'تشغيل وصيانة',   'setting-2',     'navy'],
            ['crafts',        'حرف ومهن',       'ruler-and-pen', 'brown'],
            ['seasonal_work', 'وظائف موسمية',   'snow',          'cyan'],
            ['other',         'أخرى',           'more-square',   'neutral'],
        ];

        foreach ($categories as $index => [$code, $name, $icon, $colorToken]) {
            Category::query()->updateOrCreate(
                ['code' => $code],
                [
                    'name' => ['ar' => $name],
                    'icon' => $icon,
                    'color_token' => $colorToken,
                    'sort_order' => $index,
                    'is_active' => true,
                ],
            );
        }
    }
}
