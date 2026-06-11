<?php

namespace App\Helpers;

/**
 * Pagination Helper
 *
 * A helper class to standardize pagination across the CMS
 */
class PaginationHelper
{
    /**
     * Generate pagination data
     *
     * @param int $totalItems Total number of items
     * @param int $currentPage Current page number
     * @param int $perPage Number of items per page
     * @param string $status Current status filter (optional)
     * @return array Pagination data
     */
    public static function paginate($totalItems, $currentPage = 1, $perPage = 10, $status = 'all')
    {
        // Ensure current page is valid
        $currentPage = (int)$currentPage;
        $currentPage = max(1, $currentPage);

        // Calculate offset for database queries
        $offset = ($currentPage - 1) * $perPage;

        // Calculate total pages
        $totalPages = ceil($totalItems / $perPage);

        // Return pagination data
        return [
            'page' => $currentPage,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
            'total_items' => $totalItems,
            'offset' => $offset,
            'status' => $status
        ];
    }

    /**
     * Render pagination HTML
     *
     * @param array $pagination Pagination data
     * @param string $baseUrl Base URL for pagination links
     * @param string $status Current status filter (optional)
     * @return string HTML for pagination
     */
    public static function renderHtml($pagination, $baseUrl, $status = 'all')
    {
        if ($pagination['total_pages'] <= 1) {
            return '';
        }

        $html = '<nav aria-label="Page navigation">';
        $html .= '<ul class="pagination justify-content-center">';

        // Previous button
        if ($pagination['page'] > 1) {
            $prevUrl = $baseUrl . '?page=' . ($pagination['page'] - 1);
            if ($status != 'all') {
                $prevUrl .= '&status=' . $status;
            }
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $prevUrl . '" aria-label="Previous">';
            $html .= '<span aria-hidden="true">&laquo;</span>';
            $html .= '</a>';
            $html .= '</li>';
        } else {
            $html .= '<li class="page-item disabled">';
            $html .= '<a class="page-link" href="#" aria-label="Previous">';
            $html .= '<span aria-hidden="true">&laquo;</span>';
            $html .= '</a>';
            $html .= '</li>';
        }

        // Page numbers
        for ($i = 1; $i <= $pagination['total_pages']; $i++) {
            $pageUrl = $baseUrl . '?page=' . $i;
            if ($status != 'all') {
                $pageUrl .= '&status=' . $status;
            }

            $activeClass = ($i == $pagination['page']) ? 'active' : '';
            $html .= '<li class="page-item ' . $activeClass . '">';
            $html .= '<a class="page-link" href="' . $pageUrl . '">' . $i . '</a>';
            $html .= '</li>';
        }

        // Next button
        if ($pagination['page'] < $pagination['total_pages']) {
            $nextUrl = $baseUrl . '?page=' . ($pagination['page'] + 1);
            if ($status != 'all') {
                $nextUrl .= '&status=' . $status;
            }
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $nextUrl . '" aria-label="Next">';
            $html .= '<span aria-hidden="true">&raquo;</span>';
            $html .= '</a>';
            $html .= '</li>';
        } else {
            $html .= '<li class="page-item disabled">';
            $html .= '<a class="page-link" href="#" aria-label="Next">';
            $html .= '<span aria-hidden="true">&raquo;</span>';
            $html .= '</a>';
            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= '</nav>';

        return $html;
    }

    /**
     * Generate a Smarty-compatible pagination array
     *
     * @param int $totalItems Total number of items
     * @param int $currentPage Current page number
     * @param int $perPage Number of items per page
     * @return array Pagination data for Smarty templates
     */
    public static function paginateForSmarty($totalItems, $currentPage = 1, $perPage = 10)
    {
        $pagination = self::paginate($totalItems, $currentPage, $perPage);

        return [
            'page' => $pagination['page'],
            'per_page' => $pagination['per_page'],
            'total_pages' => $pagination['total_pages'],
            'total_items' => $pagination['total_items'],
            'offset' => $pagination['offset']
        ];
    }
}
