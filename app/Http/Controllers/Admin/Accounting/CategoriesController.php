<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\PaginatedCategoryCollection;
use App\Models\Category;
use App\Models\Transaction;
use App\Rules\CategoryHasTransactions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoriesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        if ($request->exists('all')) {
            return response()->json(new CategoryCollection(Category::all()));
        }

        $categories = Category::tableFilter()->paginate($request->per_page);

        return response()->json(new PaginatedCategoryCollection($categories));
    }

    public function store()
    {
        request()->validate([
            'type' => ['required', Rule::in('in', 'out')],
            'name' => 'required|unique:categories,name',
            'icon' => 'required'
        ]);

        Category::create([
            'type' => request('type'),
            'name' => request('name'),
            'icon' => request('icon'),
        ]);

        return response()->json([], 201);
    }

    public function update(Category $category)
    {
        if ($category->id <= 13) {
            return response()->json([], 422);
        }

        request()->validate([
            'type' => [
                'required',
                Rule::in('in', 'out'),
                new CategoryHasTransactions($category->getOriginal('type') !== request('type'),
                    $category->transaction()->count())
            ],
            'name' => ['required', Rule::unique('categories', 'name')->ignore($category->id)],
            'icon' => 'required'
        ]);

        $category->update([
            'type' => request('type'),
            'name' => request('name'),
            'icon' => request('icon')
        ]);

        return response()->json([], 204);
    }

    public function destroy(Category $category)
    {
        if ($category->id <= 13) {
            return response()->json([], 422);
        }

        if ($category->transaction->count()) {
            return response()->json([], 409);
        }

        $category->delete();

        return response()->json([], 204);
    }
}
