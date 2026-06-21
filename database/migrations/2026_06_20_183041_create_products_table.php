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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code_barre')->unique();
            $table->string('nom');
            $table->decimal('prix_unitaire', 10, 2);
            $table->decimal('prix_achat', 10, 2);
            $table->integer('quantite');
            $table->integer('seuil_alerte');
             $table->foreignId('fournisseur_id')->nullable()->constrained('fournisseurs')->nullOnDelete();
            $table->date('date_expiration')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
