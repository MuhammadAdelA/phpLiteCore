<?php

declare(strict_types=1);

namespace PhpLiteCore\Pagination\Renderers;

use PhpLiteCore\Pagination\PaginatorInterface;

class JsonRenderer implements RendererInterface
{
    private string $baseUrl;

    public function __construct(string $baseUrl = '/')
    {
        // Ensure the base URL ends with a query string starter
        $this->baseUrl = rtrim($baseUrl, '?&') . (str_contains($baseUrl, '?') ? '&' : '?') . 'page=';
    }

    public function render(PaginatorInterface $paginator): string
    {
        $data = [
            'meta' => [
                'total_items' => $paginator->getTotalItems(),
                'per_page' => $paginator->getPerPage(),
                'current_page' => $paginator->getCurrentPage(),
                'last_page' => $paginator->getTotalPages(),
                'items_on_page' => $paginator->getItemsOnCurrentPage(),
            ],
            'links' => [
                'first' => $this->baseUrl . '1',
                'last' => $this->baseUrl . $paginator->getTotalPages(),
                'prev' => $paginator->hasPrevPage() ? $this->baseUrl . $paginator->getPrevPageUrl() : null,
                'next' => $paginator->hasNextPage() ? $this->baseUrl . $paginator->getNextPageUrl() : null,
            ],
        ];

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}