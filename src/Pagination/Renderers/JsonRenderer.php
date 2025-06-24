<?php

declare(strict_types=1);

namespace PhpLiteCore\Pagination\Renderers;

use PhpLiteCore\Pagination\PaginatorInterface;

class JsonRenderer implements RendererInterface
{
    public function render(PaginatorInterface $paginator): string
    {
        $baseUrl = '/api/items?page='; // Example base URL

        $data = [
            'meta' => [
                'total_items' => $paginator->getTotalItems(),
                'per_page' => $paginator->getPerPage(),
                'current_page' => $paginator->getCurrentPage(),
                'last_page' => $paginator->getTotalPages(),
                'items_on_page' => $paginator->getItemsOnCurrentPage(),
            ],
            'links' => [
                'first' => $baseUrl . '1',
                'last' => $baseUrl . $paginator->getTotalPages(),
                'prev' => $paginator->hasPrevPage() ? $baseUrl . $paginator->getPrevPageUrl() : null,
                'next' => $paginator->hasNextPage() ? $baseUrl . $paginator->getNextPageUrl() : null,
            ],
        ];

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}