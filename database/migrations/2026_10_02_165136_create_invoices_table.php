<?php

use App\Models\Supplier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //AGREGAR COLUMNAS DE CLAVE Y DESCRIPCION PARA CATALOGOS
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Supplier::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->string('track_id', 20);
            $table->string('code', 10);
            $table->string('serie', 25);
            $table->string('folio', 40);
            $table->string('payment_method', 50);
            $table->string('payment_form', 50);
            $table->string('currency', 50);
            $table->string('exchange_rate', 50); //tipo cambio
            $table->decimal('total', 30, 6);
            $table->string('uuid', 36);
            $table->boolean('is_masive');
            $table->boolean('status_sat');
            $table->string('status');
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
