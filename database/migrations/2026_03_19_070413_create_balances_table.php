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
        Schema::create('balances', function (Blueprint $table) {
            $table->id();
            $table->string('Cuenta')->nullable();
            $table->string('Descripcion')->nullable();
            $table->string('DebeP')->nullable();
            $table->string('HaberP')->nullable();
            $table->string('SaldoP')->nullable();
            $table->string('DebeA')->nullable();
            $table->string('HaberA')->nullable();
            $table->string('SaldoA')->nullable();
            $table->string('Grupo')->nullable();
            $table->string('correccion')->nullable();
            $table->string('empresa')->nullable();
            $table->string('mes')->nullable();
            $table->boolean('is_summary')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balances');
    }
};
