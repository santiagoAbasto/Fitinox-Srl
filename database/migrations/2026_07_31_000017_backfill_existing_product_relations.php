<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Preserve the cards that the public site was previously generating automatically.
     * From this point on, each set is editable from the product administration screen.
     */
    public function up(): void
    {
        $productsByCategory = DB::table('products')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->where('products.is_active', true)
            ->where('categories.is_active', true)
            ->orderBy('products.id')
            ->get(['products.id', 'products.category_id'])
            ->groupBy('category_id');

        $alreadyManaged = DB::table('product_related')->pluck('product_id')->flip();
        $now = now();

        foreach ($productsByCategory as $products) {
            foreach ($products as $product) {
                if ($alreadyManaged->has($product->id)) {
                    continue;
                }

                $relations = collect($products)
                    ->reject(fn ($candidate) => $candidate->id === $product->id)
                    // The former public query exposed the first four products in the category.
                    ->take(4)
                    ->values()
                    ->map(fn ($candidate, $index) => [
                        'product_id' => $product->id,
                        'related_product_id' => $candidate->id,
                        'sort_order' => $index,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])
                    ->all();

                if ($relations !== []) {
                    DB::table('product_related')->insert($relations);
                }
            }
        }
    }

    public function down(): void
    {
        // Intentionally left intact: these relationships become editorial CMS content.
    }
};
