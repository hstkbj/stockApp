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
        Schema::create('mouvements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('emplacement_id')->constrained('emplacements'); // emplacement source / concerné
            $table->foreignId('emplacement_destination_id')->nullable()->constrained('emplacements'); // rempli seulement si type = transfert
            $table->integer('quantite');
            $table->enum('type', ['entree', 'sortie', 'transfert']);
            $table->date('date');
            $table->string('motif')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mouvements');
    }
};
