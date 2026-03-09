<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function index(string $kitchen_slug)
    {
        $categories = Category::latest()->get();
        return view('categories.index', compact('categories'));
    }

    public function create(string $kitchen_slug)
    {
        return view('categories.create');
    }

    public function store(Request $request, string $kitchen_slug)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(string $kitchen_slug, Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, string $kitchen_slug, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(string $kitchen_slug, Category $category)
    {
        try {
            if ($category->recipes()->exists()) {
                return back()->with('error', 'Cannot delete category with associated recipes.');
            }

            if ($category->ingredients()->exists()) {
                return back()->with('error', 'Cannot delete category with associated ingredients.');
            }

            $category->delete();

            return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Category delete failed: ' . $e->getMessage(), [
                'category_id' => $category->id,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to delete category: ' . $e->getMessage());
        }
    }

    public function bulkDestroy(Request $request, string $kitchen_slug)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:categories,id',
        ]);

        $ids = $request->ids;
        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($ids as $id) {
            $category = Category::find($id);
            if (!$category) {
                continue;
            }

            // Skip deletion if used in recipes or ingredients
            if ($category->recipes()->exists() || $category->ingredients()->exists()) {
                $skippedCount++;
                continue;
            }

            $category->delete();
            $deletedCount++;
        }

        $message = "Deleted $deletedCount categories.";
        if ($skippedCount > 0) {
            $message .= " Skipped $skippedCount categories currently in use.";
        }

        return redirect()->route('categories.index')->with('success', $message);
    }
}
