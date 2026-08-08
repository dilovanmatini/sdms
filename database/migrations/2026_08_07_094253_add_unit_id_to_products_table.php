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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('category_id')->constrained('units')->restrictOnDelete();
        });

        $legacyUnits = DB::table('products')
            ->whereNotNull('unit')
            ->where('unit', '!=', '')
            ->distinct()
            ->pluck('unit');

        foreach ($legacyUnits as $unitName) {
            $unitId = DB::table('units')->where('name', $unitName)->value('id');

            if ($unitId === null) {
                $unitId = DB::table('units')->insertGetId([
                    'name' => $unitName,
                    'symbol' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('products')
                ->where('unit', $unitName)
                ->update(['unit_id' => $unitId]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('unit');
            $table->foreignId('unit_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('unit')->nullable()->after('category_id');
        });

        $products = DB::table('products')->whereNotNull('unit_id')->get(['id', 'unit_id']);

        foreach ($products as $product) {
            $name = DB::table('units')->where('id', $product->unit_id)->value('name');

            DB::table('products')
                ->where('id', $product->id)
                ->update(['unit' => $name ?? 'وحدة']);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_id');
            $table->string('unit')->nullable(false)->change();
        });
    }
};
