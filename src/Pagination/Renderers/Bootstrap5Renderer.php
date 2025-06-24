<?php

declare(strict_types=1);

namespace PhpLiteCore\Pagination\Renderers;

use PhpLiteCore\Pagination\PaginatorInterface;

class Bootstrap5Renderer implements RendererInterface
{
    public function render(PaginatorInterface $paginator): string
    {
        if (!$paginator->hasPages()) {
            return '';
        }

        $output = '<nav aria-label="Page navigation"><ul class="pagination">';
        $output .= $this->getPreviousButton($paginator);
        $output .= $this->getPageLinks($paginator); // This function will contain the main logic
        $output .= $this->getNextButton($paginator);
        $output .= '</ul></nav>';

        return $output;
    }

    private function getPageLinks(PaginatorInterface $paginator): string
    {
        // For simplicity, we'll render all page links.
        // A more complex implementation could add "..." for many pages.
        $output = '';
        for ($i = 1; $i <= $paginator->getTotalPages(); $i++) {
            $activeClass = ($i === $paginator->getCurrentPage()) ? 'active' : '';
            $output .= sprintf(
                '<li class="page-item %s"><a class="page-link" href="?page=%d">%d</a></li>',
                $activeClass,
                $i,
                $i
            );
        }
        return $output;
    }

    private function getPreviousButton(PaginatorInterface $paginator): string
    {
        $text = '&laquo;'; // Previous
        if (!$paginator->hasPrevPage()) {
            return sprintf('<li class="page-item disabled"><span class="page-link">%s</span></li>', $text);
        }

        return sprintf(
            '<li class="page-item"><a class="page-link" href="?page=%d" rel="prev">%s</a></li>',
            $paginator->getPrevPageUrl(),
            $text
        );
    }

    private function getNextButton(PaginatorInterface $paginator): string
    {
        $text = '&raquo;'; // Next
        if (!$paginator->hasNextPage()) {
            return sprintf('<li class="page-item disabled"><span class="page-link">%s</span></li>', $text);
        }

        return sprintf(
            '<li class="page-item"><a class="page-link" href="?page=%d" rel="next">%s</a></li>',
            $paginator->getNextPageUrl(),
            $text
        );
    }
}