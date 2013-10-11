<?php
class LuceneSearch_Search
{

    protected $index;

    /**
     *
     */
    public function __construct()
    {
        $this->index = new LuceneSearch_Search_Index();

        add_filter('the_posts', array($this, 'search'));
        add_action('lucene_search_daily', array($this, 'housekeeping'));

        // admin stuff
        new LuceneSearch_Admin();

    }


    /**
     * The search hook - hijack the default WP search
     * @param $posts
     * @param bool $query
     * @return array
     */
    public function search($posts, $query = false)
    {
        global $wp_query, $wp;

        $do_search = true;

        if (!is_search()) {
            $do_search = false;
        }
        if ($wp_query->is_admin) {
            $do_search = false;
        }

        if ($wp_query->query_vars['post_type'] == 'attachment' && $wp_query->query_vars['post_status'] == 'inherit,private') {
            $do_search = false;
        }

        if ($do_search) {
            $term = array();
            $term['s'] = $wp->query_vars['s']; # The search term
            $current_page = get_query_var('paged') ? (get_query_var('paged') - 1) : 0;
            $count_per_page = $wp_query->query_vars['posts_per_page'];
            $posts = array(); // reset result, ready for our query

            try {
                $query = new LuceneSearch_Search_Query($term, $current_page, $count_per_page);
                $wp_query->found_posts = $query->get_found();
                $wp_query->max_num_pages = ceil($query->get_found() / $count_per_page);
                $posts = $query->get_results();
            } catch (Exception $e) {
                $wp_query->found_posts = 0;
                $wp_query->max_num_pages = 0;
                error_log('There was an error searching the index' . $e->__toString());
            }
        }

        return $posts;

    }


    /**
     *
     */
    public function housekeeping()
    {
        $this->index->optimize();
    }

}