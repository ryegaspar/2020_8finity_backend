<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\PaginatedCategoryCollection;
use App\Models\Category;
use App\Models\Transaction;
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
        $categories = new Category;

        if ($request->exists('all')) {
            return response()->json(new CategoryCollection(Category::all()));
        }

        if ($request->sort) {
            $sort = explode(',', $request->sort);

            foreach ($sort as $item) {
                list ($sortCol, $sortDir) = explode('|', $item);
                $categories = $categories->orderBy($sortCol, $sortDir);
            }
        }

        $categories = $categories->paginate($request->per_page);

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

    public function update($id)
    {
        if ((int)request('id') <= 13) {
            return response()->json([], 422);
        }

        request()->validate([
            'type' => ['required', Rule::in('in', 'out')],
            'name' => ['required', Rule::unique('categories', 'name')->ignore($id)],
            'icon' => 'required'
        ]);

        Category::find($id)->update([
            'type' => request('type'),
            'name' => request('name'),
            'icon' => request('icon')
        ]);

        return response()->json([], 204);
    }

    public function destroy($id)
    {
        if ((int)request('id') <= 13) {
            return response()->json([], 422);
        }

        if (Transaction::where('category_id', $id)->count()) {
            return response()->json([], 409);
        }

        Category::find($id)->delete();

        return response()->json([], 204);
    }
}
