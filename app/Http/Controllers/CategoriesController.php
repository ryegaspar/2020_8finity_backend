<?php

namespace App\Http\Controllers;

use App\Category;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function show()
    {
        return response()->json(new CategoryCollection(Category::all()));
    }
}
