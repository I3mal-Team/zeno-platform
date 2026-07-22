<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

final class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['SA', '+966', 'السعودية', '🇸🇦', '5X XXX XXXX'],
            ['EG', '+20', 'مصر', '🇪🇬', '1X XXXX XXXX'],
        ];

        foreach ($countries as $index => [$iso2, $dial, $name, $flag, $placeholder]) {
            Country::query()->updateOrCreate(
                ['iso2' => $iso2],
                ['dial_code' => $dial, 'name' => $name, 'flag' => $flag, 'placeholder' => $placeholder, 'sort_order' => $index, 'is_active' => true],
            );
        }
    }
}
