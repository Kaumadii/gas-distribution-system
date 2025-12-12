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
        Schema::table('gas_types', function (Blueprint $table) {
            $table->decimal('dealer_price', 10, 2)->nullable()->after('default_price');
            $table->decimal('commercial_price', 10, 2)->nullable()->after('dealer_price');
            $table->decimal('individual_price', 10, 2)->nullable()->after('commercial_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gas_types', function (Blueprint $table) {
            $table->dropColumn(['dealer_price', 'commercial_price', 'individual_price']);
        });
    }
};
