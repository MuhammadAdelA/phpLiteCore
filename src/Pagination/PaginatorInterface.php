<?php

declare(strict_types=1);

namespace PhpLiteCore\Pagination;

interface PaginatorInterface
{
    public function getTotalItems(): int;
    public function getPerPage(): int;
    public function getCurrentPage(): int;
    public function getTotalPages(): int;
    public function getOffset(): int;
    public function getItemsOnCurrentPage(): int;
    public function hasPages(): bool;
    public function hasPrevPage(): bool;
    public function hasNextPage(): bool;
    public function getPrevPageUrl(): ?int;
    public function getNextPageUrl(): ?int;
}
