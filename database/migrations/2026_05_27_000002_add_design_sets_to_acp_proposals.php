<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── New table: design sets ────────────────────────────────────
        if (! Schema::hasTable('acp_proposal_sets')) {
            Schema::create('acp_proposal_sets', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // ── Add set_id FK to proposals ────────────────────────────────
        if (Schema::hasTable('acp_proposals') && ! Schema::hasColumn('acp_proposals', 'set_id')) {
            Schema::table('acp_proposals', function (Blueprint $table) {
                $table->foreignId('set_id')
                      ->nullable()
                      ->after('deal_id')
                      ->constrained('acp_proposal_sets')
                      ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Drop FK first, then parent table
        if (Schema::hasTable('acp_proposals') && Schema::hasColumn('acp_proposals', 'set_id')) {
            Schema::table('acp_proposals', function (Blueprint $table) {
                try {
                    $table->dropForeign(['set_id']);
                } catch (\Throwable $e) { /* already gone */ }
                $table->dropColumn('set_id');
            });
        }
        Schema::dropIfExists('acp_proposal_sets');
    }
};
