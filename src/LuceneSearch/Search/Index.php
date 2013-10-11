<?php

class LuceneSearch_Search_Index
{

    private $_index;

    /**
     *
     */
    public function __construct()
    {
        try {
            $this->_index = Zend_Search_Lucene::open(LUCENE_SEARCH_INDEX_PATH);
        } catch (Exception $e) {
            $this->_index = Zend_Search_Lucene::create(LUCENE_SEARCH_INDEX_PATH);
        }
    }


    /**
     * Update a document in the index
     * @param int $post_id
     */
    public function update($post_id = 0)
    {
        global $wpls_search;
        $post = $wpls_search->introspector->get_post($post_id);
        $query = Zend_Search_Lucene_Search_QueryParser::parse('post_id:' . $post_id);
        $hits = $this->_index->find($query);
        foreach ($hits as $hit) {
            $this->_index->delete($hit->id);
        }
        // re-add the document
        $doc = new LuceneSearch_Model_Document($post);
        $this->_index->addDocument($doc);
        $this->_index->commit();
    }


    /**
     * Remove a document from the index
     * @param int $post_id
     */
    public function remove($post_id = 0)
    {
        $query = Zend_Search_Lucene_Search_QueryParser::parse('post_id:' . $post_id);
        $hits = $this->_index->find($query);
        foreach ($hits as $hit) {
            $this->_index->delete($hit->id);
        }
        $this->_index->commit();
    }


    /**
     *
     */
    public function create_new()
    {
        $this->_index = Zend_Search_Lucene::create(LUCENE_SEARCH_INDEX_PATH);
    }


    /**
     * @param $post WP_Post
     */
    public function add($post)
    {
        $doc = new LuceneSearch_Model_Document($post);
        $this->_index->addDocument($doc);
    }


    /**
     *
     */
    public function commit()
    {
        $this->_index->commit();
    }


    /**
     *
     */
    public function optimize()
    {
        $this->_index->optimize();
    }


    /**
     * @return int
     */
    public function index_size()
    {
        return $this->_index->count();
    }


    /**
     * @return int
     */
    public function get_documents()
    {
        return $this->_index->numDocs();
    }


}