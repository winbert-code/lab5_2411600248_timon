<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('products')->orderBy('name')->paginate(12);

        return view('categories.index', ['categories' => $categories]);
    }

    public function create(): View
    {
        return view('categories.create', ['category' => new Category()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:64', 'unique:categories,name'],
            'slug' => ['nullable', 'string', 'max:64'],
            'description' => ['nullable', 'string'],
        ]);

        Category::create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name'], $data['slug'] ?? null),
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('categories.index')
            ->with('success', "{$data['name']} was added as a category.");
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', ['category' => $category]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:64', 'unique:categories,name,'.$category->id],
            'slug' => ['nullable', 'string', 'max:64'],
            'description' => ['nullable', 'string'],
        ]);

        $category->update([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name'], $data['slug'] ?? null, $category->id),
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('categories.index')
            ->with('success', "{$category->name} was updated.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return redirect()->route('categories.index')
                ->with('error', "{$category->name} cannot be removed because products still use it.");
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', "{$category->name} was removed.");
    }

    private function uniqueSlug(string $name, ?string $slug, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug !== '' && $slug !== null ? $slug : $name);
        $base = $base !== '' ? $base : 'item';
        $candidate = $base;
        $suffix = 2;

        while (Category::when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $candidate)
            ->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }
}
