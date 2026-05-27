<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('acp_proposal_sets') && ! Schema::hasColumn('acp_proposal_sets', 'page_settings')) {
            Schema::table('acp_proposal_sets', function (Blueprint $table) {
                $table->json('page_settings')->nullable()->after('page_order');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('acp_proposal_sets') && Schema::hasColumn('acp_proposal_sets', 'page_settings')) {
            Schema::table('acp_proposal_sets', function (Blueprint $table) {
                $table->dropColumn('page_settings');
            });
        }
    }
};
