<?php

namespace MyApp;

class Pagination{

    public bool $scroll_paging = false;

    /**
     * To store the html output for pagination nav
     * @var string $nav
     */
    public string $nav;

    /**
     * To store the total count of database results
     * @var int $total_rows
     */
    public int $total_rows;

    /**
     * To store and assign how many database results per page
     * @var int $page_rows
     */
    public int $page_rows;

    /**
     * Max page links that will be displayed for pagination nav before (not including next and prev links and first and last page links)
     * @var int $max_links
     */
    public int $max_links = 5;

    /**
     * To store the last page number
     * @var int $last_page
     */
    public int $last_page;

    /**
     * To store the current page number by default is 1
     * @var int $current_page
     */
    public int $current_page = 1;

    /**
     * To store the starting record for each page
     * @var int $start_record
     */
    public int $start_record;

    /**
     *If there is url provided then we need to store it to display later
     * @var string $url
     */
    public string $url = "";

    /**
     * If there is query string provided then we need to store it to display later
     * @var string $query
     */
    public string $query = "";

    /**
     * TO decide the navigation typ AJAX or Url (GET/POST) Request
     * @var bool $ajax
     */
    public bool $ajax = false;

    /**
     * What to navigate if we have multiple pagination navs
     * @var string $what
     */
    public string $what = "";

    /**
     * To stop generating the navigator after the last page is displayed
     * @var bool $last_appeared
     */
    public bool $last_appeared = false;

    /**
     * To assign the stop point depending on the max page links
     * @var bool $last_page_here
     */
    public bool $last_page_here = false;

    /**
     * To determine whether we need next page link or not
     * @var bool $need_next
     */
    public bool $need_next = false;

    /**
     * The previous link text (could be html or icon image)
     * @var string $prev_text
     */
    public string $prev_text = "<i class='las la-long-arrow-alt-right'></i>";

    /**
     * The next link text (could be html or icon image)
     * @var string $next_text
     */
    public string $next_text = "<i class='las la-long-arrow-alt-left'></i>";

    /**
     * The (GET/POST) request index for page number
     * @var string $page_link
     */
    public string $page_link = "page";

    /**
     * This is to add custom class for styling or as javascript selector
     * @var string $custom_class
     */
    public string $custom_class = "page--link";

    function __construct(int $total_rows, int $page_rows = 15){

        // Master Values
        $this->page_rows = $page_rows;
        $this->total_rows = $total_rows;

        // This tells us the page number of our last page
        $this->last_page = (int) ceil($this->total_rows / $this->page_rows);

        // This makes sure last page cannot be less than 1
        $this->last_page = ($this->last_page < 1) ?  1 : $this->last_page;

        // Check if we can ignore "Next page" and "Last page" links
        $this->last_page_here = $this->need_next = $this->last_page > $this->max_links + 1;
    }

    /**
     * To run our pagination
     * @return string
     */
    function pagination(): string
    {
        // Get current page from POST or GET variables if it was presented, else it should be = 1
        if
        (
            isset($_POST[$this->page_link])                 // _POST page number is set
            && intval($_POST[$this->page_link])             // It is a valid number
            && $_POST[$this->page_link] > 0                 // Grater than 0
            && $_POST[$this->page_link] <= $this->last_page // less or equal last page
        )
            // Set the curren page variable and strip any XXS vulnerabilities
            $this->current_page = strip_tags($_POST[$this->page_link]);
        elseif
        (
            isset($_GET[$this->page_link])                  // _GET page number is set
            && intval($_GET[$this->page_link])              // It is a valid number
            && $_GET[$this->page_link] > 0                  // Grater than 0
            && $_GET[$this->page_link] <= $this->last_page  // less or equal last page
        )
            // Set the curren page variable and strip any XXS vulnerabilities
            $this->current_page = strip_tags($_GET[$this->page_link]);
        else
            // Or current page is 1 by default
            $this->current_page = 1;


        // Starting record for SQL or MySQL is the prev page number (offset) ex. for starting 16 offset is 15
        $this->start_record = ($this->current_page - 1) * $this->page_rows;

        // This makes sure the page number isn't below 1, or more than our last page number
        if ($this->current_page < 1)
            // set it to 1 if less than 1
            $this->current_page = 1;

        else if ($this->current_page > $this->last_page)
            // Set it to last page number
            $this->current_page = $this->last_page;

        // it is the time for our navigator to be set for displaying
        return $this->nav = $this->pagination_controls($this->current_page, $this->url);
    }

    private function pagination_controls($current_page, $url): string
    {
        $page_link_structure = "<a class='$this->custom_class' href='".
            ($this->ajax ? "javascript:void(0) 'data-what='$this->what' data-page='%s' " : "$url?".$this->query.$this->page_link."=1") . "title='".l_goto_page." %s'>%s</a>";

        // If we a scroll pagination
        if ($this->scroll_paging) {
            if ($current_page != $this->last_page) {
                return sprintf($page_link_structure,($current_page + 1), ($current_page + 1), ($current_page + 1));
            }
            return "";
        }

        // Establish the pagination controls variable
        $output = ''; //Actual paging --Start--

        // If there is any need for paging while there is more than 1 page worth of results
        if($this->last_page != 1){
            /*
            First we check if page one link still there then remove unwanted links for (first page and previous page)
			If it disappeared then we generate links to the first page, and to the previous page.
            */
            if($current_page >= $this->max_links){
                $previous = $current_page - 1;

                $output .= sprintf($page_link_structure,"1", "1", "1");

                // Prev link is needed
                if($this->max_links !== $this->last_page)
                    $output .= sprintf($page_link_structure,$previous, l_goto_prev_page, $this->prev_text);
            }
            // Render clickable number links that should appear before of the current page link
            for($i = $current_page-$this->max_links; $i < $current_page; $i++){
                if($i > 0){

                    // Check if the current page grater than the max links to prepare for cleaning the unwanted links
                    if($current_page >= $this->max_links){
                        if($i == $current_page - $this->max_links)
                            continue;
                        if($current_page != $this->last_page && $i == $current_page - $this->max_links - 1)
                            continue;
                    }

                    $output .= sprintf($page_link_structure,$i, $i, $i);

                }
            }

            // Render the current page link (not clickable)
            $output .= "<a href='javascript:void(0)' class='$this->custom_class active'>".$current_page."</a>";

            // Render clickable number links that should appear after the current page number
            for($i = $current_page + 1; $i <= $this->last_page; $i++){

                $output .= sprintf($page_link_structure,$i, $i, $i);

                if($i == $this->last_page)
                    $this->last_appeared = true;

                // Stop paging when arrive to max links
                if($i >= $this->max_links && $this->last_page_here)
                    break;
            }
            // Check if there is need to generate "Next page" & "Last page" links
            $this->need_next = !$this->last_appeared && $current_page != $this->last_page && $this->need_next;
            if ($this->need_next) {
                $next = $current_page + 1;
                $output .= sprintf($page_link_structure,$next, l_goto_next_page, $this->next_text);
                $output .= sprintf($page_link_structure,$this->last_page, l_goto_page." ".$this->last_page, $this->last_page);
            }
        }
        return $output;
    }
}