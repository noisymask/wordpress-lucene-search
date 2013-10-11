<?php
class LuceneSearch_Search_Introspector
{
    /**
     * @var array
     */
    protected $post_types;

    /**
     * @param null $settings
     */
    public function __construct($post_types = null)
    {
        if( is_array($post_types) ){
            $this->post_types = $post_types;
        } else {
            throw new Exception('Plugin settings were not passed');
        }

    }

    /**
     * @param array $args
     * @return array
     */
    public function get_posts($args = array())
    {
        global $post;
        $output = array();
        $posts = $this->set_posts_query($args);
        while ($posts->have_posts()) {
            $posts->the_post();
            $post->post_title = $this->sanitize_text($post->post_title);
            $post->post_content = $this->sanitize_text($post->post_content);
            $output[] = $post;
        }
        wp_reset_query();
        return $output;
    }


    /**
     * @param $post_id
     * @return null|WP_Post
     */
    public function get_post($post_id)
    {
        $post = get_post($post_id, OBJECT);
        $post->post_title = $this->sanitize_text($post->post_title);
        $post->post_content = $this->sanitize_text($post->post_content);
        return $post;
    }


    /**
     * @param bool $args
     * @return bool|WP_Query
     */
    protected function set_posts_query($args = false)
    {
        if (!$args) {
            $args = array();
        }

        // populate with options
        if (!empty($this->post_types)) {
            $args['post_type'] = $this->post_types;
        }

        if (!empty($args)) {
            $q = new WP_Query($args);
            wp_reset_query();
            return $q;
        }
        return false;
    }


    /**
     * @param $str
     * @return mixed
     */
    protected function sanitize_text($str)
    {
        $str = strip_tags($str);
        $str = iconv(LUCENE_SEARCH_CHARSET, LUCENE_SEARCH_CHARSET . '//TRANSLIT', $str);
        $str = str_replace("\n\r", ' ', $str);
        $str = str_replace("\n", ' ', $str);
        $str = str_replace("\r", ' ', $str);
        return trim($str);
    }


    /**
     * Count the published posts in the selected post types
     * @return int
     */
    public function total_posts()
    {
        global $wpdb;
        $total = $wpdb->get_var("
          SELECT
            COUNT({$wpdb->posts}.ID)
          FROM
            {$wpdb->posts}
          WHERE
            post_status = 'publish'
          AND
            post_type IN ('" . implode("', '", $this->post_types) . "')
        ");
        return intval($total);
    }

}
