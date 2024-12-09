<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NewsAggregator\NewsAggregatorContract;
use App\Services\NewsAggregator\NewsFetchResult;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class NewsController extends Controller
{
    /**
     * @var NewsAggregatorContract
     */
    private NewsAggregatorContract $newsAggregator;

    /**
     * NewsController constructor.
     * @param NewsAggregatorContract $newsAggregator
     */
    public function __construct(NewsAggregatorContract $newsAggregator)
    {
        $this->newsAggregator = $newsAggregator;
    }

    public function index() {
        return $this->newsAggregator->getEnabledProviders();
    }
}
