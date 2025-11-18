<?php

declare(strict_types=1);

namespace PhpLiteCore\Pagination\Renderers;

use PhpLiteCore\Pagination\PaginatorInterface;

interface RendererInterface
{
    /**
     * Renders the pagination controls.
     *
     * @param PaginatorInterface $paginator The paginator instance.
     * @return string The rendered output.
     */
    public function render(PaginatorInterface $paginator): string;
}
