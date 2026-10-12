<?php

namespace Database\Seeders;

use App\Models\Clasification;
use App\Models\Company;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClasificationSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['PHM',  'Toluca',          '312', 'AA'],
            ['PBRS', 'Matamoros',       '321', 'AA'],
            ['PIN',  'Chomerics MTY',   '324', 'AA'],
            ['PHM',  'Hydraulics MTY',  '331', 'AA'],
            ['PIN',  'Toluca',          '333', 'AA'],
            ['PSM',  'Seal',            '334', 'AA'],
            ['PIN',  'Tijuana',         '636', 'AA'],
            ['PIN',  'Matamoros',       '643', 'AA'],
            ['PIN',  'FSU',             '651', 'AA'],
            ['PSM',  'SSC',             '655', 'AA'],
            ['PIN',  'Hydraulics MTY',  '664', 'AA'],
            ['PIN',  'Queretaro',       '855', 'AA'],
            ['BLDW', 'Baldwin',         '843', 'AA'],
            ['PBRS', 'EMG Qro',         '836', 'AA'],
            ['MAB',  'MABSQ',           '4005', 'AA'],
        ];

        $companies = Company::pluck('id', 'alias');

        foreach ($rows as [$alias, $location, $air, $serie]) {
            Clasification::updateOrCreate(
                ['air' => $air],
                [
                    'company_id' => $companies[$alias],
                    'location'   => $location,
                    'is_active'  => true,
                    'serie'      => $serie,
                ]
            );
        }
    }
}
