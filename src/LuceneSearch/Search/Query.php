<?php
class LuceneSearch_Search_Query
{

    protected $results;
    protected $found = 0;


    /**
     * @param null $term
     * @param int $page
     * @param int $per_page
     */
    public function __construct($term = null, $page = 0, $per_page = 10)
    {
        $this->page = $page;
        $this->per_page = $per_page;
        if ($term) {
            $query = $this->build_search_query($term);
            $this->results = $this->do_search($query);
        }
    }


    /**
     * @param $query
     * @return array
     */
    protected function do_search($query)
    {
        $index = Zend_Search_Lucene::open(LUCENE_SEARCH_INDEX_PATH);
        $cache_id = md5($query);
        // load results from cache?
        if (!$result_set = LuceneSearch_Cache::load($cache_id)) {
            try {
                $hits = $index->find($query);
                $result_set = array();
                foreach ($hits as $hit) {
                    $result_set[] = array($hit->id, $hit->score);
                }
                LuceneSearch_Cache::save($cache_id, $result_set);
            } catch (Zend_Search_Lucene_Exception $e) {
                $result_set = array();
            }

        }

        $results = array();
        $start = $this->page * $this->per_page;
        $stop = min(array($start + $this->per_page, count($result_set)));
        $this->found = count($result_set);

        for ($i = $start; $i < $stop; $i++) {
            $doc = $index->getDocument($result_set[$i][0]);
            $results[] = new LuceneSearch_Model_Post($doc);
        }

        return $results;
    }


    /**
     * @param $term
     * @return string
     */
    protected function build_search_query($term)
    {
        $settings = wpls_settings();

        $title = intval($settings['relevance']['title']);
        $content = intval($settings['relevance']['content']);

        $title_query = "post_title:({$term['s']})^{$title}";
        $content_query = "post_content:({$term['s']})^{$content}";

        $type_query = '';
        $post_type = get_query_var('post_type');
        if (!empty($post_type) && $post_type != 'any') {
            $type_query = "+post_type:({$post_type})";
        }

        //
        $query = "+($title_query $content_query) $type_query";

        try {
            $query = Zend_Search_Lucene_Search_QueryParser::parse($query);
        } catch (Zend_Search_Lucene_Search_QueryParserException $e) {
            error_log('Query syntax error: ' . $e->getMessage());
        }

        $query = Zend_Search_Lucene_Search_QueryParser::parse($query, LUCENE_SEARCH_CHARSET);
        return $query;
    }


    /**
     * @return int
     */
    public function get_found()
    {
        return intval($this->found);
    }

    /**
     * @return array
     */
    public function get_results()
    {
        return $this->results;
    }


}