<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to add optimized compound indexes for production queries.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index(['is_active', 'category_id'], 'idx_products_active_category');
            $table->index(['is_active', 'brand_id'], 'idx_products_active_brand');
            $table->index(['is_active', 'is_featured'], 'idx_products_active_featured');
            $table->index(['is_active', 'created_at'], 'idx_products_active_created');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'idx_orders_user_created');
            $table->index(['order_status', 'created_at'], 'idx_orders_status_created');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->index(['product_id', 'is_approved'], 'idx_reviews_product_approved');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index(['is_active', 'parent_id'], 'idx_categories_active_parent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('idx_categories_active_parent');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('idx_reviews_product_approved');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_user_created');
            $table->dropIndex('idx_orders_status_created');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_active_category');
            $table->dropIndex('idx_products_active_brand');
            $table->dropIndex('idx_products_active_featured');
            $table->dropIndex('idx_products_active_created');
        });
    }
};
