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
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_number', 255)->unique()->nullable();
            $table->string('empresa', 255)->unique()->nullable();
            $table->boolean('rfc_validated')->default(false);
            $table->boolean('cep_validated')->default(false);
            $table->integer('validated_by_admin')->default(0);
            $table->enum('account_type', ['person', 'company'])->default('person');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('account_number');
            $table->dropColumn('empresa');
            $table->dropColumn('rfc_validated');
            $table->dropColumn('cep_validated');
            $table->dropColumn('account_type');
            $table->dropColumn('validated_by_admin');
        });
    }

    // php artisan migrate --path=/database/migrations/2024_10_08_124923_add_fields_to_users_table.php

};
