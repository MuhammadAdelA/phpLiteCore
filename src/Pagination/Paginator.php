<?php

declare(strict_types=1);

namespace PhpLiteCore\Pagination;

use InvalidArgumentException;

class Paginator implements PaginatorInterface
{
    private int $totalItems;
    private int $perPage;
    private int $currentPage;
    private int $totalPages;

    public function __construct(int $totalItems, int $perPage, int $currentPage = 1)
    {
        if ($totalItems < 0) {
            throw new InvalidArgumentException('Total items cannot be negative.');
        }

        if ($perPage < 1) {
            throw new InvalidArgumentException('Items per page must be at least 1.');
        }

        $this->totalItems = $totalItems;
        $this->perPage = $perPage;
        $this->totalPages = (int) ceil($this->totalItems / $this->perPage);
        if ($this->totalPages < 1) {
            $this->totalPages = 1;
        }

        if ($currentPage < 1 || ($currentPage > $this->totalPages && $this->totalItems > 0)) {
            throw new InvalidArgumentException("Current page is out of bounds. Must be between 1 and {$this->totalPages}.");
        }

        $this->currentPage = $currentPage;
    }

    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function getOffset(): int
    {
        return ($this->currentPage - 1) * $this->perPage;
    }

    public function getItemsOnCurrentPage(): int
    {
        if ($this->currentPage < $this->totalPages) {
            return $this->perPage;
        }

        return $this->totalItems - (($this->totalPages - 1) * $this->perPage);
    }

    public function hasPages(): bool
    {
        return $this->totalPages > 1;
    }

    public function hasPrevPage(): bool
    {
        return $this->currentPage > 1;
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->totalPages;
    }

    public function getPrevPageUrl(): ?int
    {
        return $this->hasPrevPage() ? $this->currentPage - 1 : null;
    }

    public function getNextPageUrl(): ?int
    {
        return $this->hasNextPage() ? $this->currentPage + 1 : null;
    }
}
