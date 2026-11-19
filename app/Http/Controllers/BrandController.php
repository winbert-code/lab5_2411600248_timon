<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index(): View
    {
        $brands = Brand::withCount('products')->orderBy('name')->paginate(12);

        return view('brands.index', ['brands' => $brands]);
    }

    public function create(): View
    {
        return view('brands.create', ['brand' => new Brand()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:64', 'unique:brands,name'],
            'slug' => ['nullable', 'string', 'max:64'],
            'description' => ['nullable', 'string'],
        ]);

        Brand::create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name'], $data['slug'] ?? null),
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('brands.index')
            ->with('success', "{$data['name']} was added as a brand.");
    }

    public function edit(Brand $brand): View
    {
        return view('brands.edit', ['brand' => $brand]);
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:64', 'unique:brands,name,'.$brand->id],
            'slug' => ['nullable', 'string', 'max:64'],
            'description' => ['nullable', 'string'],
        ]);

        $brand->update([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name'], $data['slug'] ?? null, $brand->id),
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('brands.index')
            ->with('success', "{$brand->name} was updated.");
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        if ($brand->products()->exists()) {
            return redirect()->route('brands.index')
                ->with('error', "{$brand->name} cannot be removed because products still use it.");
        }

        $brand->delete();

        return redirect()->route('brands.index')
            ->with('success', "{$brand->name} was removed.");
    }

    private function uniqueSlug(string $name, ?string $slug, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug !== '' && $slug !== null ? $slug : $name);
        $base = $base !== '' ? $base : 'item';
        $candidate = $base;
        $suffix = 2;

        while (Brand::when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $candidate)
            ->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }
}
