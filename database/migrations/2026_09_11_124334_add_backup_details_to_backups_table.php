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
        Schema::table('backups', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('id')->index();
            $table->string('disk')->after('status');
            $table->string('path')->nullable()->after('disk');
            $table->string('filename')->nullable()->after('path');
            $table->unsignedBigInteger('size')->nullable()->after('filename');
            $table->text('error_message')->nullable()->after('size');
            $table->foreignId('created_by')->nullable()->after('error_message')->constrained('users')->nullOnDelete();
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status']);
            $table->dropColumn([
                'status',
                'disk',
                'path',
                'filename',
                'size',
                'error_message',
            ]);
        });
    }
};
