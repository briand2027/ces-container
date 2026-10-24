<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('demandes_devis', function (Blueprint $table) {
            $table->timestamp('conditions_acceptees_at')->nullable();
            $table->string('conditions_version', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('demandes_devis', function (Blueprint $table) {
            $table->dropColumn(['conditions_acceptees_at', 'conditions_version']);
        });
    }
};
