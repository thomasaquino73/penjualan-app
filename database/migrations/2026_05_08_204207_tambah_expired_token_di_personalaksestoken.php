<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // 👇 tambahan sesuai gambar
            $table->tinyInteger('expired_token')->default(0)->after('abilities'); // 0 = aktif, 1 = expired
            $table->timestamp('expired_token_at')->nullable()->after('expired_token');
        });
    }

    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropColumn('expired_token');
            $table->dropColumn('expired_token_at');
        });
    }
};
