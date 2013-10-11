<?php
class LuceneSearch_Admin
{
    /**
     * @var LuceneSearch_Search_Index
     */
    protected $index;

    /**
     * @var LuceneSearch_Search_Introspector
     */
    protected $introspector;

    /**
     * @var int
     */
    protected $last_indexed;

    /**
     *
     */
    public function __construct()
    {

        $this->settings = wpsl_settings();

        $this->index = new LuceneSearch_Search_Index();
        $this->introspector = new LuceneSearch_Search_Introspector( $this->settings['post_types'] );

        add_action('admin_menu', array(&$this, 'admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_js'));
        add_action('wp_ajax_lucene_search_index', array($this, 'index_callback'));

        add_action('publish_post', array($this, 'on_post_saved'));
        add_action('publish_page', array($this, 'on_post_saved'));
        add_action('edit_post', array($this, 'on_post_saved'));
        add_action('delete_post', array($this, 'on_post_deleted'));

    }


    /**
     *
     */
    public function index_callback()
    {
        set_time_limit(30);

        $return = array();
        $start_id = intval($_POST['start_id']);
        $count = intval($_POST['count']);
        $this->last_indexed = intval($_POST['last_indexed']);

        if ($start_id == 0) {
            $this->index->create_new();
        }

        // get total posts
        $return['total_posts'] = $this->introspector->total_posts();

        $args = array(
            'orderby' => 'ID',
            'order' => 'ASC',
            'post_status' => 'publish',
            'posts_per_page' => $this->settings['batch_size']
        );

        add_filter('posts_where', array($this, 'filter_since_id'));
        $posts = $this->introspector->get_posts($args);
        remove_filter('posts_where', array($this, 'filter_since_id'));

        foreach ($posts as $p) {
            $count++;
            $this->index->add($p);
        }

        $this->index->commit();
        $return['last_indexed'] = isset($p) ? $p->ID : 0;
        $return['count'] = $count;

        echo json_encode($return);
        exit;
    }


    /**
     * @param string $where
     * @return string
     */
    public function filter_since_id($where = '')
    {
        if (is_admin() && $this->last_indexed > 0) {
            global $wpdb;
            $this->last_indexed = intval($this->last_indexed);
            $where .= " AND {$wpdb->posts}.ID > {$this->last_indexed}";
        }
        return $where;
    }


    /**
     *
     */
    public function admin_options()
    {

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        if (!empty($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'save-settings')) {
            $this->save_settings();
            echo '<div id="message" class="updated"><p>Settings saved.</p></div>';
        }

        if (!empty($_REQUEST['indexed']) && $_REQUEST['indexed'] == 1) {
            echo '<div id="message" class="updated"><p>Indexing complete.</p></div>';
        }


        ?>
        <div class="wrap">

            <div id="icon-options-general" class="icon32"><br/></div>
            <h2>Wordpress Lucene Search</h2>

            <h3>Index</h3>

            <p>Index size: <?php echo $this->index->index_size(); ?></p>

            <p>Documents: <?php echo $this->index->get_documents(); ?></p>

            <p><input type="button" id="start-indexing" value="Build Index" class="button-primary"/><img
                  src="<?php echo esc_url(LUCENE_SEARCH_PLUGIN_URL) . 'img/3.gif'; ?>" id="loading-gif"></p>

            <p id="progress-documents"></p>

            <p id="bar"></p>

            <hr>

            <h3>Settings</h3>

            <form action="<?php echo admin_url('options-general.php?page=wordpress-lucene-search'); ?>" method="post">
                <?php wp_nonce_field('save-settings'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Post Types</th>
                        <td><?php $this->post_type_checkboxes(); ?></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Batch Size</th>
                        <td>
                            <input type="text" name="<?php esc_attr_e(LUCENE_SEARCH_SETTINGS_KEY); ?>[batch_size]"
                                   value="<?php esc_attr_e((int)$this->settings['batch_size']); ?>"/>
                            <em>Lower if you encounter timeouts</em>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Relevance</th>
                        <td>
                            <label>Title <input type="text" name="<?php esc_attr_e(
                                    LUCENE_SEARCH_SETTINGS_KEY
                                ); ?>[relevance][title]" value="<?php esc_attr_e(
                                    (int)$this->settings['relevance']['title']
                                ); ?>"/></label><br>
                            <label>Content <input type="text" name="<?php esc_attr_e(
                                    LUCENE_SEARCH_SETTINGS_KEY
                                ); ?>[relevance][content]" value="<?php esc_attr_e(
                                    (int)$this->settings['relevance']['content']
                                ); ?>"/></label>
                        </td>
                    </tr>
                </table>
                <p><input type="submit" value="Save Settings" class="button-primary"/></p>
            </form>

        </div> <!-- .wrap -->
    <?php
    }


    /**
     *
     */
    public function admin_js($hook)
    {
        if (stristr($hook, 'options-general.php')) {
            return;
        }

        wp_enqueue_script('wordpress-lucene-search', LUCENE_SEARCH_PLUGIN_URL . 'js/admin.js', array('jquery'), LUCENE_SEARCH_VERSION);
    }


    /**
     *
     */
    private function post_type_checkboxes()
    {
        $post_types = get_post_types(
            array(
                'public' => true,
                '_builtin' => false
            ),
            'names'
        );
        $post_types[] = 'post';
        $post_types[] = 'page';
        foreach ($post_types as $post_type) {
            $checked = (in_array($post_type, (array)$this->settings['post_types'])) ? " checked='checked' " : "";
            echo sprintf('<label><input type="checkbox" name="%s[post_types][]" value="%s" %s /> %s</label><br />', LUCENE_SEARCH_SETTINGS_KEY, $post_type, $checked, $post_type);
        }
    }


    /**
     *
     */
    private function save_settings()
    {
        $form_data = $_POST[LUCENE_SEARCH_SETTINGS_KEY];

        $form_data['batch_size'] = ($form_data['batch_size'] < 1) ? 1 : $form_data['batch_size'];
        $form_data['relevance']['title'] = ($form_data['relevance']['title'] < 1) ? 1 : $form_data['relevance']['title'];
        $form_data['relevance']['content'] = ($form_data['relevance']['content'] < 1) ? 1 : $form_data['relevance']['content'];

        if (get_option(LUCENE_SEARCH_SETTINGS_KEY) !== false) {
            update_option(LUCENE_SEARCH_SETTINGS_KEY, $form_data);
        } else {
            add_option(LUCENE_SEARCH_SETTINGS_KEY, $form_data, '', 'no');
        }

        $this->settings = wpsl_settings();
    }





    /**
     * @param $post_id
     */
    public function on_post_saved($post_id)
    {
        $this->index->update($post_id);
    }


    /**
     * @param $post_id
     */
    public function on_post_deleted($post_id)
    {
        $this->index->remove($post_id);
    }


    /**
     *
     */
    public function admin_menu()
    {
        add_options_page(
            'Wordpress Lucene Search',
            'Wordpress Lucene Search',
            'manage_options',
            'wordpress-lucene-search',
            array(&$this, 'admin_options')
        );
    }


}