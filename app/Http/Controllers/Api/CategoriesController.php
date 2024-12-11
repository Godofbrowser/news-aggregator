<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function index() {
        $models = Category::query()->get();
        return response()->json($models->map(function ($item) {
            return [
                'id' => $item['id'],
                'name' => $item['name'],
            ];
        }));
    }
}
