<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('demandes_devis', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 40)->unique();
            $table->string('type_client', 20);
            $table->string('nom', 100);
            $table->string('prenom', 100)->nullable();
            $table->string('nom_entreprise', 200)->nullable();
            $table->string('email');
            $table->string('telephone', 30)->nullable();
            $table->text('adresse');
            $table->string('ville', 100);
            $table->string('code_postal', 30);
            $table->string('pays', 100);
            $table->string('numero_tva', 50)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('duree_location_jours')->nullable();
            $table->string('statut', 30)->default('nouveau')->index();
            $table->timestamps();
        });

        Schema::create('lignes_demandes_devis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_devis_id')->constrained('demandes_devis')->cascadeOnDelete();
            $table->unsignedBigInteger('conteneur_id')->nullable()->index();
            $table->string('reference_conteneur', 120);
            $table->string('type_conteneur', 120)->nullable();
            $table->string('type_ligne', 20);
            $table->unsignedInteger('quantite');
            $table->decimal('prix_unitaire_indicatif', 12, 2)->nullable();
            $table->unsignedInteger('duree_location_jours')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lignes_demandes_devis');
        Schema::dropIfExists('demandes_devis');
    }
};
