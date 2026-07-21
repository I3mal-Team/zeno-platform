<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Lists whose codes are branched on in application logic. Admins may relabel
 * and reorder them; `is_system` blocks deletion.
 */
final class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seed('work_types', [
            ['full_time', 'دوام كامل'],
            ['part_time', 'دوام جزئي'],
            ['daily', 'عمل يومي'],
            ['weekly', 'عمل أسبوعي'],
            ['seasonal', 'عمل موسمي'],
            ['temporary', 'عمل مؤقت'],
            ['hourly', 'عمل بالساعة'],
        ]);

        $this->seed('salary_units', [
            ['monthly', 'شهرياً'],
            ['weekly', 'أسبوعياً'],
            ['daily', 'يومياً'],
            ['hourly', 'للساعة'],
        ]);

        $this->seed('gender_requirements', [
            ['all', 'متاح للجميع'],
            ['men', 'رجال'],
            ['women', 'نساء'],
        ]);

        $this->seed('nationality_requirements', [
            ['all', 'جميع الجنسيات'],
            ['saudi', 'سعودي فقط'],
            ['non_saudi', 'غير سعودي'],
        ]);

        foreach ([1, 3, 5, 10, 20, 50] as $index => $km) {
            DB::table('search_radii')->updateOrInsert(
                ['value_km' => $km],
                ['sort_order' => $index, 'is_active' => true, 'updated_at' => now(), 'created_at' => now()],
            );
        }

        $this->seedReportReasons();
    }

    /** @param  list<array{0: string, 1: string}>  $rows */
    private function seed(string $table, array $rows): void
    {
        foreach ($rows as $index => [$code, $name]) {
            DB::table($table)->updateOrInsert(
                ['code' => $code],
                [
                    'name' => json_encode(['ar' => $name], JSON_UNESCAPED_UNICODE),
                    'sort_order' => $index,
                    'is_active' => true,
                    'is_system' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }
    }

    private function seedReportReasons(): void
    {
        $reasons = [
            ['job_duplicate', 'إعلان مكرر', 'job'],
            ['job_misleading', 'معلومات مضللة', 'job'],
            ['job_inappropriate', 'محتوى غير مناسب', 'job'],
            ['job_asks_for_fees', 'يطلب رسوماً من المتقدمين', 'job'],
            ['organization_fake', 'منشأة غير حقيقية', 'organization'],
            ['user_impersonation', 'انتحال شخصية', 'user'],
            ['message_harassment', 'مضايقة أو إساءة', 'message'],
            ['message_spam', 'رسائل مزعجة', 'message'],
            ['other', 'سبب آخر', 'job'],
        ];

        foreach ($reasons as $index => [$code, $name, $target]) {
            DB::table('report_reasons')->updateOrInsert(
                ['code' => $code],
                [
                    'name' => json_encode(['ar' => $name], JSON_UNESCAPED_UNICODE),
                    'target_type' => $target,
                    'sort_order' => $index,
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }
    }
}
