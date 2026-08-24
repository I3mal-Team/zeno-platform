<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Riyadh districts are seeded from a known list rather than reverse-geocoded,
 * so job locations do not depend on a map provider's neighbourhood data.
 */
final class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            ['riyadh', 'الرياض', 'الرياض', 46.6753, 24.7136],
            ['jeddah', 'جدة', 'مكة المكرمة', 39.1925, 21.4858],
            ['makkah', 'مكة المكرمة', 'مكة المكرمة', 39.8579, 21.3891],
            ['madinah', 'المدينة المنورة', 'المدينة المنورة', 39.6142, 24.5247],
            ['dammam', 'الدمام', 'الشرقية', 50.1033, 26.4207],
            ['khobar', 'الخبر', 'الشرقية', 50.2083, 26.2794],
            ['dhahran', 'الظهران', 'الشرقية', 50.1550, 26.2361],
            ['taif', 'الطائف', 'مكة المكرمة', 40.4158, 21.2703],
            ['buraydah', 'بريدة', 'القصيم', 43.9730, 26.3260],
            ['tabuk', 'تبوك', 'تبوك', 36.5550, 28.3838],
            ['abha', 'أبها', 'عسير', 42.5053, 18.2465],
            ['khamis_mushait', 'خميس مشيط', 'عسير', 42.7333, 18.3000],
            ['hail', 'حائل', 'حائل', 41.6900, 27.5114],
            ['najran', 'نجران', 'نجران', 44.1322, 17.4917],
            ['jubail', 'الجبيل', 'الشرقية', 49.6600, 27.0046],
        ];

        foreach ($cities as $index => [$code, $name, $region, $lng, $lat]) {
            DB::table('cities')->updateOrInsert(
                ['code' => $code],
                [
                    'name' => json_encode(['ar' => $name], JSON_UNESCAPED_UNICODE),
                    'region' => $region,
                    'sort_order' => $index,
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );

            DB::statement(
                'UPDATE cities SET center_point = ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography WHERE code = ?',
                [$lng, $lat, $code],
            );
        }

        $this->seedRiyadhDistricts();
    }

    private function seedRiyadhDistricts(): void
    {
        $cityId = DB::table('cities')->where('code', 'riyadh')->value('id');

        $districts = [
            ['olaya', 'حي العليا', 46.6853, 24.6944],
            ['malqa', 'حي الملقا', 46.6203, 24.7743],
            ['yasmin', 'حي الياسمين', 46.6390, 24.8226],
            ['nakheel', 'حي النخيل', 46.6320, 24.7440],
            ['hittin', 'حي حطين', 46.6010, 24.7580],
            ['sahafa', 'حي الصحافة', 46.6420, 24.8010],
            ['ghadir', 'حي الغدير', 46.6600, 24.7690],
            ['wurud', 'حي الورود', 46.6700, 24.7250],
            ['murabba', 'حي المربع', 46.7060, 24.6560],
            ['sulaymaniyah', 'حي السليمانية', 46.7000, 24.7010],
            ['rawdah', 'حي الروضة', 46.7690, 24.7350],
            ['naseem', 'حي النسيم', 46.8180, 24.7280],
            ['shifa', 'حي الشفا', 46.6870, 24.5620],
            ['aziziyah', 'حي العزيزية', 46.7420, 24.5810],
            ['badiah', 'حي البديعة', 46.6390, 24.6300],
            ['irqah', 'حي عرقة', 46.5700, 24.6960],
            ['diriyah', 'حي الدرعية', 46.5750, 24.7370],
            ['qurtubah', 'حي قرطبة', 46.7900, 24.8080],
            ['munsiyah', 'حي المونسية', 46.8300, 24.8250],
            ['khaleej', 'حي الخليج', 46.7960, 24.7570],
        ];

        foreach ($districts as $index => [$code, $name, $lng, $lat]) {
            DB::table('districts')->updateOrInsert(
                ['city_id' => $cityId, 'code' => $code],
                [
                    'name' => json_encode(['ar' => $name], JSON_UNESCAPED_UNICODE),
                    'sort_order' => $index,
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );

            DB::statement(
                'UPDATE districts SET center_point = ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography WHERE city_id = ? AND code = ?',
                [$lng, $lat, $cityId, $code],
            );
        }
    }
}
