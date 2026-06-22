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
        Schema::create('ventes', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->decimal('montant_total', 10, 2);
            $table->decimal('montant_paye', 10, 2)->default(0);
            $table->decimal('monnaie', 10, 2)->default(0);
            $table->enum('status', ['paye', 'non_paye'])->default('non_paye');
            $table->date('date');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('vente_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vente_id')->constrained('ventes');
            $table->foreignId('product_id')->constrained('products');
            $table->integer('quantite');
            $table->decimal('prix_unitaire', 10, 2);
            $table->decimal('prix_total', 10, 2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventes');
    }
};
