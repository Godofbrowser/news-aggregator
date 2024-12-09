<?php

namespace App\Services\NewsAggregator;

class NewsFetchResult
{
    /**
     * The news result data
     * - title
     * - source
     * - date
     * - category
     * - author
     * - -
     * - thumbnail
     * - url
     * @var mixed
     */
    private $data;

    /**
     * @var int
     */
    private int $page;

    /**
     * @var int
     */
    private int $per_page;

    /**
     * @var int
     */
    private int $total_page;

    /**
     * NewsFetchResult constructor.
     * @param $data
     * @param $page
     * @param $per_page
     * @param $total_page
     */
    public function __construct($data, $page, $per_page, $total_page)
    {
        $this->data = $data;
        $this->page = $page;
        $this->per_page = $per_page;
        $this->total_page = $total_page;
    }

    public function getPage(): int {
        return $this->page;
    }

    public function getData(): mixed {
        return $this->data;
    }

    public function hasNextPage(): bool {
        return $this->total_page > $this->page;
    }

    public function toArray(): array {
        return [
            'data' => $this->data,
            'page' => $this->page,
            'per_page' => $this->per_page,
            'total_page' => $this->total_page,
        ];
    }
}
