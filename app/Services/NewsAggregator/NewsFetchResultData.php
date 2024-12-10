<?php

namespace App\Services\NewsAggregator;

use Illuminate\Support\Carbon;

class NewsFetchResultData
{
    public ?string $category_name = null;
    public ?string $link = null;
    public ?string $thumbnail = null;
    public ?string $provider = null;
    public ?string $provider_id = null;
    public ?string $headline = null;
    public ?string $body = null;
    public ?Carbon $published_at = null;

    public function toArray() {
        return get_object_vars($this);
    }
}
