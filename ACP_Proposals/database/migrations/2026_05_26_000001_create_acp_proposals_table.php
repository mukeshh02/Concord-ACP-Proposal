<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('acp_proposals')) {
            Schema::create('acp_proposals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('deal_id')->nullable()->index();
                $table->string('title');
                $table->string('status')->default('draft'); // draft | ready | sent
                $table->json('data');            // all form fields as JSON
                $table->string('pdf_path')->nullable(); // last generated PDF path
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('acp_proposals');
    }
};
