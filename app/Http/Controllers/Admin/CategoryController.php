<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::query()
            ->withCount(['products', 'subcategories'])
            ->with(['subcategories' => function ($q) {
                $q->withCount('products')->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return view('admin.categories', compact('categories'));
    }

    // ── Categories ──────────────────────────────────────────────

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
        ]);

        Category::create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug(Category::class, $data['name']),
        ]);

        return back()->with('success', 'Category added.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)],
        ]);

        // Keep the slug in step with the name only when the name actually changed,
        // so existing slugs (and any links built from them) stay stable otherwise.
        if ($data['name'] !== $category->name) {
            $category->slug = $this->uniqueSlug(Category::class, $data['name'], $category->id);
        }
        $category->name = $data['name'];
        $category->save();

        return back()->with('success', 'Category updated.');
    }

    public function destroyCategory(Category $category)
    {
        // Safe delete: a category cascades to its products (products.category_id is
        // cascadeOnDelete) and its subcategories. Refuse while anything is attached
        // so real product data can never be wiped out by removing a category.
        if ($category->products()->exists()) {
            return back()->with('error', 'Cannot delete a category that still has products. Move or remove its products first.');
        }
        if ($category->subcategories()->exists()) {
            return back()->with('error', 'Cannot delete a category that still has subcategories. Delete its subcategories first.');
        }

        $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    // ── Subcategories ───────────────────────────────────────────

    public function storeSubcategory(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:100',
        ]);

        Subcategory::create([
            'category_id' => $data['category_id'],
            'name'        => $data['name'],
            'slug'        => $this->uniqueSlug(Subcategory::class, $data['name']),
        ]);

        return back()->with('success', 'Subcategory added.');
    }

    public function updateSubcategory(Request $request, Subcategory $subcategory)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:100',
        ]);

        if ($data['name'] !== $subcategory->name) {
            $subcategory->slug = $this->uniqueSlug(Subcategory::class, $data['name'], $subcategory->id);
        }
        $subcategory->category_id = $data['category_id'];
        $subcategory->name = $data['name'];
        $subcategory->save();

        return back()->with('success', 'Subcategory updated.');
    }

    public function destroySubcategory(Subcategory $subcategory)
    {
        // Safe delete: removing a subcategory sets products.subcategory_id to null
        // (nullOnDelete). Refuse while products are attached so nothing is silently
        // un-tagged behind the admin's back.
        if ($subcategory->products()->exists()) {
            return back()->with('error', 'Cannot delete a subcategory that still has products. Move or remove its products first.');
        }

        $subcategory->delete();

        return back()->with('success', 'Subcategory deleted.');
    }

    /**
     * Build a URL slug from $name that is unique on $model's `slug` column,
     * appending -2, -3, … on collision (ignoring the row being updated).
     */
    private function uniqueSlug(string $model, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'item';
        $slug = $base;
        $i = 2;

        while ($model::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
