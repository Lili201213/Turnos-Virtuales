<?php

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
    Schema::create('turnos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('servicio_id')->constrained('servicios');
        $table->foreignId('modulo_id')->nullable()->constrained('modulos')->nullOnDelete();
        $table->unsignedInteger('numero');
        $table->string('codigo', 10);
        $table->string('nombre_ciudadano', 100)->nullable();
        $table->string('documento', 20)->nullable();
        $table->enum('estado', ['espera', 'llamado', 'atendiendo', 'finalizado', 'cancelado'])
              ->default('espera');
        $table->date('fecha');
        $table->timestamp('llamado_at')->nullable();
        $table->timestamp('finalizado_at')->nullable();
        $table->timestamps();

        $table->unique(['servicio_id', 'fecha', 'numero']);
        $table->index(['estado', 'fecha']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnos');
    }
};
