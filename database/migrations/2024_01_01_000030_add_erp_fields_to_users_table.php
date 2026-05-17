<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->unique()->after('id');
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('avatar_path')->nullable()->after('password');
            $table->string('status', 30)->default('active')->index()->after('avatar_path');
            $table->boolean('is_super_admin')->default(false)->index()->after('status');
            $table->foreignId('current_school_id')->nullable()->after('is_super_admin')->constrained('schools')->nullOnDelete();
            $table->timestamp('last_login_at')->nullable()->after('current_school_id');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->boolean('force_password_change')->default(false)->after('last_login_ip');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('current_school_id');
            $table->dropColumn([
                'uuid',
                'phone',
                'avatar_path',
                'status',
                'is_super_admin',
                'last_login_at',
                'last_login_ip',
                'force_password_change',
                'deleted_at',
            ]);
        });
    }
};
