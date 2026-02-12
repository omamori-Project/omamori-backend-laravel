<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('files', function (Blueprint $table) {
            // FK 이름 명시 (down 안정)
            $table->foreign('omamori_id', 'files_omamori_id_fk')
                ->references('id')
                ->on('omamoris')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropForeign('files_omamori_id_fk');
        });
    }
};