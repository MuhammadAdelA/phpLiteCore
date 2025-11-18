<?php

declare(strict_types=1);

namespace PhpLiteCore\Pagination\Renderers;

use PhpLiteCore\Pagination\PaginatorInterface;

/**
 * Renders pagination controls compatible with Bootstrap 5.
 * Implements a "sliding window" of page links for large page sets.
 */
class Bootstrap5Renderer implements RendererInterface
{
    /**
     * Renders the full pagination component.
     *
     * @param PaginatorInterface $paginator
     * @return string
     */
    public function render(PaginatorInterface $paginator): string
    {
        if (! $paginator->hasPages()) {
            return '';
        }

        $output = '<nav aria-label="Page navigation"><ul class="pagination">';
        $output .= $this->getPreviousButton($paginator);
        $output .= $this->getPageLinks($paginator);
        $output .= $this->getNextButton($paginator);
        $output .= '</ul></nav>';

        return $output;
    }

    /**
     * Generates the main page number links with a sliding window.
     *
     * @param PaginatorInterface $paginator
     * @return string
     */
    private function getPageLinks(PaginatorInterface $paginator): string
    {
        $output = '';
        $totalPages = $paginator->getTotalPages();
        $currentPage = $paginator->getCurrentPage();

        // Number of links to show on each side of the current page.
        $window = 2;

        if ($totalPages <= ((2 * $window) + 1)) {
            // If there are not enough pages to need truncation, show all page links.
            for ($i = 1; $i <= $totalPages; $i++) {
                $output .= $this->getPageLink($i, $currentPage);
            }
        } else {
            // --- Complex case: Render a sliding window of links ---

            // Always show the first page link.
            $output .= $this->getPageLink(1, $currentPage);

            // Show starting ellipsis if needed.
            if ($currentPage > $window + 2) {
                $output .= $this->getDisabledLink('...');
            }

            // The sliding window of links around the current page.
            $start = max(2, $currentPage - $window);
            $end = min($totalPages - 1, $currentPage + $window);
            for ($i = $start; $i <= $end; $i++) {
                $output .= $this->getPageLink($i, $currentPage);
            }

            // Show ending ellipsis if needed.
            if ($currentPage < $totalPages - $window - 1) {
                $output .= $this->getDisabledLink('...');
            }

            // Always show the last page link.
            $output .= $this->getPageLink($totalPages, $currentPage);
        }

        return $output;
    }

    /**
     * Generates the "Previous" page button.
     *
     * @param PaginatorInterface $paginator
     * @return string
     */
    private function getPreviousButton(PaginatorInterface $paginator): string
    {
        $text = '&laquo;'; // Previous symbol

        if (! $paginator->hasPrevPage()) {
            return $this->getDisabledLink($text);
        }

        return sprintf(
            '<li class="page-item"><a class="page-link" href="?page=%d" rel="prev">%s</a></li>',
            $paginator->getPrevPageUrl(), // Should be getPrevPageNumber() ideally
            $text
        );
    }

    /**
     * Generates the "Next" page button.
     *
     * @param PaginatorInterface $paginator
     * @return string
     */
    private function getNextButton(PaginatorInterface $paginator): string
    {
        $text = '&raquo;'; // Next symbol

        if (! $paginator->hasNextPage()) {
            return $this->getDisabledLink($text);

        }

        return sprintf(
            '<li class="page-item"><a class="page-link" href="?page=%d" rel="next">%s</a></li>',
            $paginator->getNextPageUrl(), // Should be getNextPageNumber() ideally
            $text
        );
    }

    /**
     * Helper method to generate a single, active or inactive page link.
     *
     * @param int $page
     * @param int $currentPage
     * @return string
     */
    private function getPageLink(int $page, int $currentPage): string
    {
        $activeClass = ($page === $currentPage) ? 'active' : '';
        $ariaCurrent = ($page === $currentPage) ? 'aria-current="page"' : '';

        return sprintf(
            '<li class="page-item %s"><a class="page-link" href="?page=%d" %s>%d</a></li>',
            $activeClass,
            $page,
            $ariaCurrent,
            $page
        );
    }

    /**
     * Helper method to generate a disabled list item (for ellipsis or disabled buttons).
     *
     * @param string $text
     * @return string
     */
    private function getDisabledLink(string $text): string
    {
        return sprintf('<li class="page-item disabled"><span class="page-link">%s</span></li>', $text);
    }
}
