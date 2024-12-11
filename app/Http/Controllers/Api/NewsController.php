<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\FilterArticlesRequest;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function index(FilterArticlesRequest $request) {
        return $this->getFeed($request);
    }

    public function getStats() {
        return response()->json([
            'total_articles' => Article::query()->count(),
            'total_categories' => Category::query()->count(),
            'total_sources' => Article::query()->distinct('provider')->count('provider'),
        ]);
    }

    public function getFeed(FilterArticlesRequest $request) {
        $query = Article::query();
        $authUser = $request->user(); // Todo:: use the user's preference for relevancy

        if ($category = $request->query('category')) {
            $query = $query->where('category_id', $category);
        }
        if ($source = $request->query('source')) {
            $query = $query->where('provider', $source);
        }
        if ($sort = $request->query('sortBy')) {
            if ($sort === 'latest') {
                $query = $query->orderBy('published_at', 'desc');
            }
            else {
                $query = $query->inRandomOrder(); // Todo: use user's preference
            }
        }
        if ($search = $request->query('query')) {
            $query = $query->where('headline', 'like', "%$search%")
                ->orWhere('content', 'like', "%$search%");
        }

        $articles = $query->with('category')->paginate(12);

        // Todo: move to APiResource
        return response()->json(Collection::make($articles->items())->map(function ($item) {
            return [
                'id' => $item['id'],
                'source' => $item['provider'],
                'category' => $item['category']['name'],
                'title' => $item['headline'],
                'content' => $item['body'],
                'thumbnail' => $item['thumbnail'],
                'publishedAt' => $item['published_at'],
                'url' => $item['link'],
            ];
        }));
    }

    public function getSources() {
        $models = Article::query()->distinct('provider')->get(['provider']);
        return response()->json($models->map(function ($item) {
            return [
                'id' => $item['provider'],
                'name' => Str::title(str_replace('_', ' ', $item['provider'])),
            ];
        }));
    }
}
