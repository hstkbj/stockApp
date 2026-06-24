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
        Schema::create('invoice_mecefs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');

            // Données envoyées à l'API (étape 1)
            $table->string('uid')->nullable();              // UID reçu après POST
            $table->string('invoice_type')->default('FV'); // FV, FA, EV, EA
            $table->string('payment_type')->default('ESPECES');

            // Réponse de finalisation (étape 3)
            $table->string('code_mecef_dgi')->nullable();  // ex: X537-E4DB-AJUU-HHXN-FWIS-FEKJ
            $table->string('qr_code')->nullable();         // contenu du QR Code
            $table->string('nim')->nullable();             // NIM de l'e-MCF
            $table->string('counters')->nullable();        // ex: 64/64 FV
            $table->timestamp('mecef_datetime')->nullable();

            // Montants retournés par l'API (pour vérification)
            $table->integer('total_mecef')->nullable();    // total calculé par l'API
            $table->integer('vat_b')->nullable();          // TVA groupe B
            $table->integer('ht_b')->nullable();           // HT groupe B

            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_mecefs');
    }
};
