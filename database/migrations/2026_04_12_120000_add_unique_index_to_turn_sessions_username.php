<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $duplicateUsernames = DB::table('turn_sessions')
            ->select('username')
            ->groupBy('username')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('username');

        foreach ($duplicateUsernames as $username) {
            $ids = DB::table('turn_sessions')
                ->where('username', $username)
                ->orderBy('id')
                ->pluck('id');

            $first = true;
            foreach ($ids as $id) {
                if ($first) {
                    $first = false;
                    continue;
                }

                DB::table('turn_sessions')
                    ->where('id', $id)
                    ->update([
                        'username' => $username . ':' . $id,
                        'updated_at' => now(),
                    ]);
            }
        }

        Schema::table('turn_sessions', function (Blueprint $table) {
            $table->unique('username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('turn_sessions', function (Blueprint $table) {
            $table->dropUnique('turn_sessions_username_unique');
        });
    }
};
