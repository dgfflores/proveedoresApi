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
            ['PHM',  'TOLUCA',          '0312', 'AA'],
            ['PBRS', 'MATAMOROS',       '0321', 'AA'],
            ['PIN',  'CHOMERICS MTY',   '0324', 'AA'],
            ['PHM',  'HYDRAULICS MTY',  '0331', 'AA'],
            ['PIN',  'TOLUCA',          '0333', 'AA'],
            ['PSM',  'SEAL',            '0334', 'AA'],
            ['PIN',  'TIJUANA',         '0636', 'AA'],
            ['PIN',  'MATAMOROS',       '0643', 'AA'],
            ['PIN',  'FSU',             '0651', 'AA'],
            ['PSM',  'SSC',             '0655', 'AA'],
            ['PIN',  'HYDRAULICS MTY',  '0664', 'AA'],
            ['PIN',  'QUERETARO',       '0855', 'AA'],
            ['BLDW', 'BALDWIN',         '0843', 'AA'],
            ['PBRS', 'EMG QRO',         '0836', 'AA'],
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
