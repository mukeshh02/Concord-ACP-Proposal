<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('acp_proposal_sets') && ! Schema::hasColumn('acp_proposal_sets', 'layout')) {
            Schema::table('acp_proposal_sets', function (Blueprint $table) {
                $table->json('layout')->nullable()->after('is_active');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('acp_proposal_sets') && Schema::hasColumn('acp_proposal_sets', 'layout')) {
            Schema::table('acp_proposal_sets', function (Blueprint $table) {
                $table->dropColumn('layout');
            });
        }
    }
};
