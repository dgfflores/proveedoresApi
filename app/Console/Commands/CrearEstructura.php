<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Services\FolderStructureService;

#[Signature('app:crear-estructura')]
#[Description('Command Crea la estructura de carpetas necesaria para el proyecto')]
class CrearEstructura extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $disk = Storage::disk('local');
        $service = new FolderStructureService($disk);
        $service->generateMonthlyStructure();
        $this->info('Estructura de carpetas creada exitosamente.');

        return Command::SUCCESS;
    }
}
