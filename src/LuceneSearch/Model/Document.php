<?php
/**
 * Class LuceneSearch_Model_Document
 *
 * Create Lucene doc model from Wordpress post object
 */
class LuceneSearch_Model_Document extends Zend_Search_Lucene_Document
{

    /**
     * @param WP_Post $post
     */
    public function __construct(WP_Post $post)
    {
        $this->addField(Zend_Search_Lucene_Field::Keyword('post_id', $post->ID));
        $this->addField(Zend_Search_Lucene_Field::UnIndexed('post_author', $post->post_author));
        $this->addField(Zend_Search_Lucene_Field::UnIndexed('post_date', $post->post_date));
        $this->addField(Zend_Search_Lucene_Field::UnIndexed('post_date_gmt', $post->post_date_gmt));
        $this->addField(Zend_Search_Lucene_Field::Text('post_content', $post->post_content));
        $this->addField(Zend_Search_Lucene_Field::Text('post_title', $post->post_title));
        $this->addField(Zend_Search_Lucene_Field::UnIndexed('post_category', $post->post_category));
        $this->addField(Zend_Search_Lucene_Field::UnIndexed('post_excerpt', $post->post_excerpt));
        $this->addField(Zend_Search_Lucene_Field::UnIndexed('post_status', $post->post_status));
        $this->addField(Zend_Search_Lucene_Field::UnIndexed('comment_status', $post->comment_status));
        $this->addField(Zend_Search_Lucene_Field::UnIndexed('ping_status', $post->ping_status));
        $this->addField(Zend_Search_Lucene_Field::UnIndexed('post_name', $post->post_name));
        $this->addField(Zend_Search_Lucene_Field::UnIndexed('to_ping', $post->to_ping));
        $this->addField(Zend_Search_Lucene_Field::UnIndexed('pinged', $post->pinged));
        $this->addField(Zend_Search_Lucene_Field::UnIndexed('post_modified', $post->post_modified));
        $this->addField(Zend_Search_Lucene_Field::UnIndexed('post_modified_gmt', $post->post_modified_gmt));
        $this->addField(Zend_Search_Lucene_Field::UnIndexed('post_content_filtered', $post->post_content_filtered));
        $this->addField(Zend_Search_Lucene_Field::UnIndexed('post_parent', $post->post_parent));
        $this->addField(Zend_Search_Lucene_Field::UnIndexed('guid', $post->guid));
        $this->addField(Zend_Search_Lucene_Field::UnIndexed('menu_order', $post->menu_order));
        $this->addField(Zend_Search_Lucene_Field::Text('post_type', $post->post_type));
        $this->addField(Zend_Search_Lucene_Field::UnIndexed('post_mime_type', $post->post_mime_type));
        $this->addField(Zend_Search_Lucene_Field::UnIndexed('comment_count', $post->comment_count));
    }


}
