<?php
/**
 * WP Post structure used to send hits back to Wordpress search results
 *
 */

class LuceneSearch_Model_Post
{

    public $ID;
    public $post_author;
    public $post_date;
    public $post_date_gmt;
    public $post_content;
    public $post_title;
    public $post_excerpt;
    public $post_status;
    public $comment_status;
    public $ping_status;
    public $post_name;
    public $to_ping;
    public $pinged;
    public $post_modified;
    public $post_modified_gmt;
    public $post_content_filtered;
    public $post_parent;
    public $guid;
    public $menu_order;
    public $post_type;
    public $post_mime_type;
    public $comment_count;

    /**
     * @param Zend_Search_Lucene_Document $doc
     */
    public function __construct(Zend_Search_Lucene_Document $doc)
    {
        $this->ID = $doc->getFieldValue('post_id');
        $this->post_author = $doc->getFieldValue('post_author');
        $this->post_date = $doc->getFieldValue('post_date');
        $this->post_date_gmt = $doc->getFieldValue('post_date_gmt');
        $this->post_content = $doc->getFieldValue('post_content');
        $this->post_title = $doc->getFieldValue('post_title');
        $this->post_excerpt = $doc->getFieldValue('post_excerpt');
        $this->post_status = $doc->getFieldValue('post_status');
        $this->comment_status = $doc->getFieldValue('comment_status');
        $this->ping_status = $doc->getFieldValue('ping_status');
        $this->post_name = $doc->getFieldValue('post_name');
        $this->to_ping = $doc->getFieldValue('to_ping');
        $this->pinged = $doc->getFieldValue('pinged');
        $this->post_modified = $doc->getFieldValue('post_modified');
        $this->post_modified_gmt = $doc->getFieldValue('post_modified_gmt');
        $this->post_content_filtered = $doc->getFieldValue('post_content_filtered');
        $this->post_parent = $doc->getFieldValue('post_parent');
        $this->guid = $doc->getFieldValue('guid');
        $this->menu_order = $doc->getFieldValue('menu_order');
        $this->post_type = $doc->getFieldValue('post_type');
        $this->post_mime_type = $doc->getFieldValue('post_mime_type');
        $this->comment_count = $doc->getFieldValue('comment_count');
    }

}
