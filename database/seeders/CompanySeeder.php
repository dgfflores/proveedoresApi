<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            ['rfc' => 'XAXX010101000', 'bussiness_name' => 'Parker Hannifin de México SA de CV', 'alias' => 'PHM', 'is_active' => true],
            ['rfc' => 'XAXX010101001', 'bussiness_name' => 'Parker Brownsville Servicios SA de CV', 'alias' => 'PBRS', 'is_active' => true],
            ['rfc' => 'XAXX010101002', 'bussiness_name' => 'Parker Industrial S de RL de CV', 'alias' => 'PIN', 'is_active' => true],
            ['rfc' => 'XAXX010101003', 'bussiness_name' => 'Parker Servicios de México SA de CV', 'alias' => 'PSM', 'is_active' => true],
            ['rfc' => 'XAXX010101004', 'bussiness_name' => 'Baldwin Filters S de RL de CV', 'alias' => 'BLDW', 'is_active' => true],
            ['rfc' => 'XAXX010101005', 'bussiness_name' => 'Meggitt Aircraft Brakyng Systems Queretaro S de RL de CV', 'alias' => 'MAB', 'is_active' => true],

        ];

        DB::table('companies')->insert($companies);
    }
}
