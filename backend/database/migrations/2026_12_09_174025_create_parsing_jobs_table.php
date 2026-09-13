<?php

use App\Models\Organization;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('parsing_jobs', function(Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Organization::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->string('status')->default('queued');

            $table->integer('reviews_fetched')->default(0);
            $table->integer('reviews_total_estimate')->nullable();

            $table->unsignedTinyInteger('attempt')->default(1);
            $table->text('error_message')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parsing_jobs');
    }
};
