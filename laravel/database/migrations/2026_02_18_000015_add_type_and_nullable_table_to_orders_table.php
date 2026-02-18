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
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['table_id']);
            $table->unsignedBigInteger('table_id')->nullable()->change();
            $table->foreign('table_id')->references('id')->on('tables')->nullOnDelete();
            $table->string('tipo')->default('mesa')->after('table_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['table_id']);
            $table->unsignedBigInteger('table_id')->nullable(false)->change();
            $table->foreign('table_id')->references('id')->on('tables')->cascadeOnDelete();
            $table->dropColumn('tipo');
        });
    }
};
