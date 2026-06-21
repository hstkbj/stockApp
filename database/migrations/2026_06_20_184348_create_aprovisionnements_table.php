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
        Schema::create('aprovisionnements', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('fournisseur_id')->constrained('fournisseurs');
            $table->decimal('montant_total', 10, 2);
            $table->dateTime('date_approvisionnement');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('status',['livrer','enAttente','envoyer']);
            $table->timestamps();
        });

        Schema::create('aprovisionnement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aprovisionnement_id')->constrained('aprovisionnements');
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
        Schema::dropIfExists('aprovisionnements');
    }
};
