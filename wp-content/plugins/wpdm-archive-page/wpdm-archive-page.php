<?php
/*
Plugin Name: WPDM - Archive Page
Description: Add archive page option with wordpress download manager
Plugin URI: https://www.wpdownloadmanager.com/download/wpdm-directory-add-on/
Author: Shaon
Version: 2.9.9
Author URI: https://www.wpdownloadmanager.com/
Text Domain: wpdm-archive-page
*/

global $wpdm_archive_page;

class WPDM_ArchivePage{

    function __construct(){
        $this->Actions();
        $this->ShortCodes();
    }

    /**
     * @usage Initiate Action Hooks
     */
    function Actions(){
        add_action('plugins_loaded', array($this, 'LoadTextdomain') );
        add_action('wpdm_ext_shortcode', array($this, 'MCEButtonHelper'));
        add_action("wp_ajax_wpdm_change_cat_parent", array( $this, 'ChangeCatParent'));
        add_action("wp_ajax_nopriv_wpdm_change_cat_parent", array( $this, 'ChangeCatParent'));
        add_action("wp_ajax_load_ap_content", array( $this, 'APSContent'));
        add_action("wp_ajax_nopriv_load_ap_content", array( $this, 'APSContent'));
        add_action("init",  array($this, 'GetChildCats'));
        add_action('basic_settings', array($this, 'LinkTemplateOption'));
        add_action('wp_loaded',array($this, 'getDownloads'));
        add_action('wp_head',array($this, 'WPHead'));
        add_action('widgets_init', function(){ register_widget("WPDM_SearchWidget"); });
        add_filter("posts_where", array($this, "Where"));
    }

    /**
     * @usage Introduce All Short-codes
     */
    function ShortCodes(){
        add_shortcode( 'wpdm-archive', array($this, '_archivePage'));
        add_shortcode( 'wpdm_archive', array($this, '_archivePage'));
        add_shortcode( 'wpdm-categories', array($this, 'categories'));
        add_shortcode( 'wpdm_categories', array($this, 'categories'));
        add_shortcode( 'wpdm_category_blocks', array($this, 'categoryBlocks'));
        add_shortcode( 'wpdm-tags', array($this, 'Tags'));
        add_shortcode( 'wpdm_tags', array($this, 'Tags'));
        add_shortcode( 'wpdm-search-page', array($this, 'SeachBar'));
        add_shortcode( 'wpdm_search_page', array($this, 'SeachBar'));
        add_shortcode( 'wpdm_simple_search', array($this, 'simpleSeachBar'));
    }

    /**
     * @usage Load Language File
     */
    function LoadTextdomain(){
        load_plugin_textdomain('wpdm-archive-page', WP_PLUGIN_URL . "/wpdm-archive-page/languages/", 'wpdm-archive-page/languages/');
    }

    /**
     * @usage Template for Archive Page Sidebar View
     */
    function _archivePageWithSidebar($params = array()){
        ob_start();
        include(wpdm_tpl_path('archive-page-with-sidebar.php', dirname(__FILE__).'/tpls/'));
        return ob_get_clean();
    }

    /**
     * @usage Category and Package Details Template for Archive Page Sidebar View
     */
    function APSContent(){
        if(isset($_POST['cid']))
            include(wpdm_tpl_path('aps-content-cat.php', dirname(__FILE__).'/tpls/'));
        if(isset($_POST['pid']))
            include(wpdm_tpl_path('aps-content-pack.php', dirname(__FILE__).'/tpls/'));
        die();
    }

    /**
     * @param int $parent
     * @param string $btype
     * @param int $base
     * @usage Render WPDM Category List
     */
    function renderCats($parent=0, $btype = 'default', $base = 0, $showcount = 1){
        global $wpdb, $current_user;
        $user_role = isset($current_user->roles[0]) ? $current_user->roles[0] : 'guest';

        $args = array(
            'orderby'       => 'name',
            'order'         => 'ASC',
            'hide_empty'    => false,
            'exclude'       => array(),
            'exclude_tree'  => array(),
            'include'       => array(),
            'number'        => '',
            'fields'        => 'all',
            'slug'          => '',
            'parent'        => $parent,
            'hierarchical'  => true,
            'get'           => '',
            'name__like'    => '',
            'pad_counts'    => false,
            'offset'        => '',
            'search'        => '',
            'cache_domain'  => 'core'
        );
        $categories = get_terms('wpdmcategory',$args);

        if( is_array( $categories ) ){

            if( $parent != $base )
                echo "<ul class='wpdm-dropdown-menu collapse'  id='collapse-{$parent}'>";

            foreach($categories as $category) {
                if(\WPDM\Package::userCanAccess($category->term_id, 'category') || get_option('_wpdm_hide_all', 0) == 0){
                $cld = get_term_children( $category->term_id, 'wpdmcategory' );
                $ccount = $category->count;
                $link = get_term_link($category);
                ?>

                <li class="wpdm-cat-item">
                    <div class="btn-group text-left" style="width: 100%">
                        <a style="width: <?php echo (count($cld) > 0)?'calc(100% - 50px)':'100%'; ?>" class="wpdm-cat-link text-left btn  btn-<?php echo $btype; ?>" rel='<?php echo $category->term_id; ?>' href="<?php echo $link; ?>">
                            <?php echo stripcslashes($category->name); ?> <?php if((int)$showcount == 1) echo " ($ccount)"; ?>
                        </a>
                        <?php if(count($cld) > 0): ?>
                            <a style="width: 50px" class=" btn  btn-<?php echo $btype; ?>"  data-toggle="collapse" href="#collapse-<?php echo $category->term_id; ?>" role="button" aria-expanded="false" aria-controls="collapse-<?php echo $category->term_id; ?>">
                                <i class="fa fa-chevron-down"></i>
                            </a>
                        <?php endif; ?>
                    </div>


                <?php $this->renderCats($category->term_id, $btype, $base, $showcount);

                    echo '</li>';
                }
            }

            if($parent != $base) echo "</ul>" ;
        }
    }

    /**
     * @param int $parent
     * @param string $btype
     * @param int $base
     * @usage Render WPDM Category List
     */
    function catDropdown($parent=0, $btype = 'default', $base = 0, $showcount = 1){
        global $wpdb, $current_user;
        $user_role = isset($current_user->roles[0]) ? $current_user->roles[0] : 'guest';

        $args = array(
            'orderby'       => 'name',
            'order'         => 'ASC',
            'hide_empty'    => false,
            'exclude'       => array(),
            'exclude_tree'  => array(),
            'include'       => array(),
            'number'        => '',
            'fields'        => 'all',
            'slug'          => '',
            'parent'        => $parent,
            'hierarchical'  => true,
            'get'           => '',
            'name__like'    => '',
            'pad_counts'    => false,
            'offset'        => '',
            'search'        => '',
            'cache_domain'  => 'core'
        );
        $categories = get_terms('wpdmcategory',$args);

        if( is_array( $categories ) ){

        if( $parent != $base )
            echo "<ul class='dropdown-menu'  id='collapse-{$parent}'>";

        foreach($categories as $category) {
        if(\WPDM\Package::userCanAccess($category->term_id, 'category') || get_option('_wpdm_hide_all', 0) == 0){
        $cld = get_term_children( $category->term_id, 'wpdmcategory' );
        $ccount = $category->count;
        $link = get_term_link($category);
        ?>

    <li class="wpdm-cat-item <?php if($parent ==0) echo 'col-md-4'; else if(count($cld) > 0) echo 'dropdown-submenu'; ?>" style="margin: 0">
    <?php if(count($cld) > 0 && $parent == 0){ ?>
    <div class="btn-group text-left" style="width: 100%">
    <a style="width: <?php echo (count($cld) > 0)?'calc(100% - 40px)':'100%'; ?>" class="wpdm-cat-link text-left  <?php if($parent ==0) echo ' btn btn-block btn-'.$btype; ?>" rel='<?php echo $category->term_id; ?>' href="<?php echo $link; ?>">
    <?php echo stripcslashes($category->name); ?> <?php if((int)$showcount == 1) echo " ($ccount)"; ?>
    </a>
        <?php if($parent ==0) { ?>
    <a style="width: 40px" class=" btn  btn-<?php echo $btype; ?>"  data-toggle="collapse" href="#collapse-<?php echo $category->term_id; ?>" role="button" aria-expanded="false" aria-controls="collapse-<?php echo $category->term_id; ?>">
        <i class="fa fa-chevron-down"></i>
    </a>
    <?php } else { ?>
            <a style="width: 50px" class="pull-right float-right"  data-toggle="collapse" href="#collapse-<?php echo $category->term_id; ?>" role="button" aria-expanded="false" aria-controls="collapse-<?php echo $category->term_id; ?>">
                <i class="fa fa-chevron-right"></i>
            </a>
        <?php } ?>
    </div>
    <?php } else { ?>
        <?php //if(count($cld) == 0){ ?>
    <a class="wpdm-cat-link text-left <?php if($parent ==0) echo ' btn btn-block btn-'.$btype; ?>" rel='<?php echo $category->term_id; ?>' href="<?php echo $link; ?>">
        <?php echo stripcslashes($category->name); ?> <?php if((int)$showcount == 1) echo " ($ccount)"; ?>
    </a>

    <?php } ?>


    <?php $this->catDropdown($category->term_id, $btype, $base, $showcount);

    echo '</li>';
    }
    }

    if($parent != $base) echo "</ul>" ;
    }
    }

    /**
     * @param array $params
     * @return string
     * @usage Short-code callback function for [wpdm_categories]
     */
    function categories($params = array()){
        global $wpdb;
        @extract($params);
        $parent = isset($parent)?$parent:0;
        $args = array(
            'orderby'       => 'name',
            'order'         => 'ASC',
            'hide_empty'    => false,
            'exclude'       => array(),
            'exclude_tree'  => array(),
            'include'       => array(),
            'number'        => '',
            'fields'        => 'all',
            'slug'          => '',
            'parent'         => $parent,
            'hierarchical'  => false,
            'child_of'      => 0,
            'get'           => '',
            'name__like'    => '',
            'pad_counts'    => false,
            'offset'        => '',
            'search'        => '',
            'cache_domain'  => 'core'
        );
        $categories = get_terms('wpdmcategory',$args);
        $pluginsurl = plugins_url();
        $cols = isset($cols) && $cols > 0 ? $cols : 2;
        $scols = intval( 12 / $cols );

        //$btn_classes = isset($btn_style) ? " btn btn-sm btn-inverse btn-block" : "";

        $icon = isset($icon) ? "<style>.wpdm-all-categories li{background: url('{$icon}') left center no-repeat;}</style>" : "";
        $k = 0;
        $html = "
        {$icon}
        <div  class='wpdm-all-categories wpdm-categories-{$cols}col'><div class='row'>";
        foreach($categories as $id => $category){
            $catlink = get_term_link($category);
            if($category->parent == $parent) {

                $count = (isset($showcount) && (int)$showcount == 1) ? "&nbsp;(".$category->count.")" : "";
                $html .= "<div class='col-md-{$scols} cat-div'><a class='wpdm-pcat' href='$catlink' >".htmlspecialchars(stripslashes($category->name)).$count."</a>";

                if(isset($subcat) && $subcat == 1) {
                    $sargs = array(
                        'orderby' => 'name',
                        'order' => 'ASC',
                        'hide_empty' => false,
                        'fields' => 'all',
                        'hierarchical' => false,
                        'child_of' => $category->term_id,
                        'pad_counts' => false
                    );
                    $subcategories = get_terms('wpdmcategory', $sargs);
                    $html .= "<div class='wpdm-subcats'>";
                    foreach ($subcategories as $sid => $subcategory) {
                        $scatlink = get_term_link($subcategory);
                        $subcat_count = (isset($showcount) && (int)$showcount == 1) ? "&nbsp;(".$subcategory->count.")" : "";
                        $html .= "<a class='wpdm-scat' href='$scatlink' >" . htmlspecialchars(stripslashes($subcategory->name)) . $subcat_count . "</a>";
                    }
                    $html .= "</div>";
                }
                $html .= "</div>";
                $k++;
            }
        }

        $html .= "</div><div style='clear:both'></div></div>";
        if($k == 0) $html = '';
        return "<div class='w3eden'>".str_replace(array("\r","\n"),"",$html)."</div>";
    }

    function categoryBlocks($params = array()){
        if(!isset($params['categories'])) return '';
        $categories = explode(",", $params['categories']);

        foreach ($categories as $i => $category){
            $categories[$i] = get_term_by('slug', $category, 'wpdmcategory');
        }
        ob_start();
 
        include "tpls/wpdmap-category-blocks.php";
        return ob_get_clean();
    }

    /**
     * @param array $params
     * @return mixed
     * @usage Short-code callback function for [wpdm_archive]
     */
    function _archivePage($params = array()){
        global $wpdb;
        @extract($params);

        $showcount = isset($showcount)?(int)$showcount:0;

        if(isset($login) && (int)$login === 1 && !is_user_logged_in()) return wpdm_login_form(array('redirect' => $_SERVER['REQUEST_URI']));

        $cat_view = isset( $cat_view ) && in_array( $cat_view, array('hidden','compact','extended','sidebar', 'legacy' ) ) ? $cat_view : 'extended';

        // 1. Category View - Sidebar
        if( $cat_view == 'sidebar' ) return $this->_archivePageWithSidebar($params);

        $button_style = isset( $button_style ) ? $button_style : 'default';

        if( isset( $category ) ){

            if( intval( $category ) == 0 && $category != ''){
                $cat = get_term_by("slug", $category, "wpdmcategory");
                $category = is_object($cat) && isset($cat->term_id) ? $cat->term_id : 0;
            }
        }

        $category = isset( $category ) ? $category : 0;
        $link_template = isset( $link_template ) ? $link_template : '';
        $items_per_page = isset( $items_per_page ) ? $items_per_page : 0;

        update_post_meta( get_the_ID(), "__wpdm_link_template", $link_template );
        update_post_meta( get_the_ID(), "__wpdm_items_per_page", $items_per_page );

        update_post_meta( get_the_ID(), '__wpdm_ac_params', $params );

        if( isset( $order ) ) {
            update_post_meta( get_the_ID(), '__wpdm_z_order', $order );
        }
        else {
            update_post_meta( get_the_ID(), '__wpdm_z_order', '' );
        }

        if( isset( $order_by ) ) {
            update_post_meta( get_the_ID(), '__wpdm_z_order_by', $order_by );
        }
        else {
            update_post_meta( get_the_ID(), '__wpdm_z_order_by', '' );
        }

        $pluginsurl = plugins_url();

        $comcat = '';
        $sw = 6;

        if($cat_view == 'extended'){
            ob_start();
            include \WPDM\Template::locate("archive-page-extended.php", dirname(__FILE__).'/tpls/');
            return ob_get_clean();
        }

        /*
        if($cat_view == 'legacy'){
            ob_start();
            include \WPDM\Template::locate("archive-page-legacy.php", dirname(__FILE__).'/tpls/');
            return ob_get_clean();
        }
        */

        // 2. Category View - Compact
        if($cat_view == 'compact') {
            $catsdd = wp_dropdown_categories(array('show_option_none' => __( "Select category" , "download-manager" ), 'hierarchical' => 1, 'show_count' => 0, 'orderby' => 'name', 'echo' => 0, 'class' => 'form-control wpdm-custom-select', 'taxonomy' => 'wpdmcategory', 'hide_empty' => 0, 'name' => 'wpdm-cats-compact', 'id' => 'wpdm-cats-compact', 'selected' => ''));
            $comcat = "<div class='col-md-3'>".'<label for="wpdm-cats-compact">'.__('Category:','wpdm-archive-page').'</label>'.$catsdd."</div>";
            $sw = 3;
        }

        $html = '
        <div class="w3eden">
        <form id="srcp" style="margin-bottom: 10px">
        <div class="row">
        <input type="hidden" name="category" id="initc" value="'.$category.'" />
        <span class="col-md-'.$sw.'">
        <label for="src">'.__('Search','wpdm-archive-page').':</label>
        <div class="input-group input-src"> 
        <input type="text" class="form-control" name="src" placeholder="'.__('Search','wpdm-archive-page').'" id="src">
          <div class="input-group-append input-group-btn">
            <button class="btn" type="submit"><i class="fas fa-search"></i></button>
          </div>
        </div>
        </span>

        '.$comcat.'

        <span class="col-md-3">
        <label for="order_by">'.__('Order By:','wpdm-archive-page').'</label>
        <select name="order_by" id="order_by" class="form-control wpdm-custom-select">
        <option value="date">'.__('Publish Date','wpdm-archive-page').'</option>
        <option value="title">'.__('Title','wpdm-archive-page').'</option>
        <option value="modified">'.__('Last Updated','wpdm-archive-page').'</option>
        <option value="view_count">'.__('View Count','wpdm-archive-page').'</option>
        <option value="download_count">'.__('Download Count','wpdm-archive-page').'</option>
        <option value="package_size_b">'.__('Package Size','wpdm-archive-page').'</option>
        </select>
        </span>
        <span class="col-md-3">
        <label for="order">'.__('Order:','wpdm-archive-page').'</label>
        <select name="order" id="order" class="form-control wpdm-custom-select">
        <option value="DESC">'.__('Descending Order','wpdm-archive-page').'</option>
        <option value="ASC">'.__('Ascending Order','wpdm-archive-page').'</option>
        </select>
        </span>

        </div><br class="clear"/>
        </form>
        <div class="row">
        <div class="col-md-12">
        <div class="breadcrumb" style="font-size: 11px">
        <a href="#" id="wpdm-archive-page-home">'.__('Home','wpdm-archive-page').'</a> <i class="fas fa-caret-right"></i>
        <span id="inp">'.__('All Downloads','wpdm-archive-page').'</span>
        </div>
        </div>
        </div>';

        // 3. Category View - Extended
        if($cat_view == 'extended') {
            $html .= '<div class="wpdm-categories"><ul class="wpdm-cat-tree">';
            ob_start();
            $this->renderCats($category, $button_style, $category, $showcount);
            $html .= ob_get_clean();
            $html .= "</ul><div class='clear'></div></div><div class='clear'><br/></div>";
        }
        // 3. Category View - Legacy
        if($cat_view == 'legacy') {
            $html .= '<div class="wpdm-categories"><ul class="wpdm-cat-dropdown row">';
            ob_start();
            $this->catDropdown($category, $button_style, $category, $showcount);
            $html .= ob_get_clean();
            $html .= "</ul><div class='clear'></div></div><div class='clear'><br/></div>";
        }
        $html .="<div class='wpdm-downloads row' id='wpdm-downloads'>".__('Select category or search','wpdm-archive-page')."...</div></div>

 
";

        return str_replace( array( "\r", "\n" ), "", $html );
    }

    function WhereOld($where){
        //return $where;
        if(!isset($_GET['wpdmtask'])||$_GET['wpdmtask']!='get_downloads') return $where;
        if(!isset($_GET['search'])||$_GET['search']=='') return $where;

        $where = str_replace(array("\n", "\r",""), "", $where);
        $where = str_replace("AND (   ( wp_postmeta.meta_key","OR (   ( wp_postmeta.meta_key", $where);
        $where = str_replace("AND wp_posts.post_type = 'wpdmpro'",") AND wp_posts.post_type = 'wpdmpro'", $where);

        if(strpos($where, "AND wp_posts.post_type = 'wpdmpro'"))
            $where = str_replace("(((wp_posts.post_title LIKE","((((wp_posts.post_title LIKE", $where);

        return $where;
    }

    function Where($where){

        global $wpdb;

        //print_r($wpdb->prefix);

        if( (!isset($_GET['wpdmtask']) || $_GET['wpdmtask'] != 'get_downloads') && !isset($_GET['q']) ) return $where;
        if( (!isset($_GET['search']) || $_GET['search'] == '') && !isset($_GET['q']) ) return $where;

        // Return because advanced search does not have a search keyword
        if( isset($_GET['q']) && $_GET['q'] == '') return $where;

        $where = str_replace(array("\n", "\r",""), "", $where);
        $where = str_replace("AND (   ( ".$wpdb->prefix,"OR (   ( ".$wpdb->prefix, $where);
        $where = str_replace(")) AND ".$wpdb->prefix, ") ) ) AND ".$wpdb->prefix, $where);
        if(strpos($where, "post_type = 'wpdmpro'")) $where = str_replace("(((".$wpdb->prefix,"((((".$wpdb->prefix, $where);

        return $where;
    }


    /**
     * @usage Fetch Packages
     */
    function getDownloads(){

        if(!isset($_GET['wpdmtask'])||$_GET['wpdmtask']!='get_downloads') return;

        //if( !defined( 'DOING_AJAX' )) return;

        global $wpdb, $current_user;

        $sparams = maybe_unserialize(get_post_meta((int)$_GET['pg'], '__wpdm_ac_params', true));

        $actpl =  get_option('_wpdm_ap_search_page_template','link-template-default.php');
        $tctpl = get_post_meta((int)$_GET['pg'],'__wpdm_link_template', true);

        $item_per_page = get_post_meta((int)$_GET['pg'],'__wpdm_items_per_page', true);

        if($tctpl!='') $actpl = $tctpl;

        $category = isset($_REQUEST['category']) && $_REQUEST['category'] != 0 ? (int)($_REQUEST['category']) : '';
        $src = isset($_GET['search']) ? esc_html($_GET['search']) : '';

        $item_per_page =  $item_per_page<=0?10:$item_per_page;

        $page = isset($_GET['cp'])?(int)$_GET['cp']:1;
        $start = ($page-1)*$item_per_page;
        $params = array("post_status" => "publish", "post_type"=>"wpdmpro","posts_per_page"=>$item_per_page,"offset"=>$start);

        //order parameter
        $order = get_post_meta((int)$_GET['pg'],'__wpdm_z_order',true);
        $order_by = get_post_meta((int)$_GET['pg'],'__wpdm_z_order_by',true);

        $order = isset($_GET['order']) ? esc_attr($_GET['order']) : $order;
        $order_by = isset($_GET['order_by']) ? esc_attr($_GET['order_by']) : $order_by;

        if(isset($order_by) && $order_by != '') {
            //order parameter
            if($order_by == 'view_count' || $order_by == 'download_count' || $order_by == 'package_size_b'){
                $params['meta_key'] = '__wpdm_' . $order_by;
                $params['orderby'] = 'meta_value_num';
            }
            else {
                $params['orderby'] = $order_by;
            }
            if($order == '') $order = 'ASC';
            $params['order'] = $order;

        }


        $params['post_type'] = 'wpdmpro';
        if($src!=''){
            $params['s'] = esc_sql($src);
            $params['meta_query'] = array(
                'relation' => 'OR',
                array(
                    'key'     => '__wpdm_files',
                    'value'   => $src,
                    'compare' => 'LIKE',
                ),
                array(
                    'key'     => '__wpdm_fileinfo',
                    'value'   => $src,
                    'compare' => 'LIKE',
                ),
            );
        }

        if($category != ''){
            $params['tax_query'] = array(array(
                'taxonomy' => 'wpdmcategory',
                'field'    => 'term_id',
                'terms'    => array( $category ),
                'operator' => 'IN',
            ));
        }

        //echo '<pre>';print_r($params);echo "</pre>";

        $q = new WP_Query($params);

        //echo '<pre>';print_r($q->request);echo "</pre>";

        $total = $q->found_posts;

        $pages = ceil($total/$item_per_page);

        $args = array(
            'base'               => '%_%',
            'format'             => '?cp=%#%',
            'total'              => $pages,
            'current'            => $page,
            'show_all'           => false,
            'end_size'           => 2,
            'mid_size'           => 1,
            'prev_next'          => true,
            'prev_text'          => __('Previous'),
            'next_text'          => __('Next'),
            'type'               => 'array',
            'add_args'           => false,
            'add_fragment'       => '',
            'before_page_number' => '',
            'after_page_number'  => ''
        );
        $pags = paginate_links($args);
        $phtml = "";
        if(is_array($pags)) {
            foreach ($pags as $pagl) {
                $phtml .= "<li>{$pagl}</li>";
            }
        }
        $phtml = "<div class='text-center'><ul class='pagination  pagination-centered text-center'>{$phtml}</ul></div>";
        $html = '';
        $role = @array_shift(array_keys($current_user->caps));

        while ( $q->have_posts() ){
            $q->the_post();

            //$package_role = maybe_unserialize(get_post_meta(get_the_ID(), '__wpdm_access', true));
            //if(is_array($package_role) && !in_array('guest', $package_role) && !in_array($role, $package_role)) {
            //    continue;
            // }

            /*
            $ext = "_blank";
            $data = wpdm_custom_data(get_the_ID());
            $data += get_post(get_the_ID(), ARRAY_A);
            $data['download_count'] = isset($data['download_count'])?$data['download_count']:0;
            $data['ID'] = get_the_ID();
            $data['id'] = get_the_ID();
            if(isset($data['files']) && count($data['files']) > 0 ){
                $tmpvar = $data['files'];
                $tmpvar = is_array($tmpvar)?array_shift($tmpvar):'';
                $tmpvar = explode(".",$tmpvar);
                $ext = count($tmpvar) > 1 ? end($tmpvar) : $ext;
            }

            $link_label = isset($data['link_label']) ? stripslashes($data['link_label']) : __('Download', 'wpdm-archive-page');

            $data['page_url'] = get_permalink(get_the_ID());

            $data['files'] = isset($data['files']) ? maybe_unserialize($data['files']):array();
            */

            $colss = array(1 => 12, 2 => 6, 3 => 4, 4 => 3);
            $cols = isset($sparams['cols'])?$sparams['cols']:1;
            $cols = isset($colss[$cols])?$colss[$cols]:12;


            $templates = maybe_unserialize(get_option("_fm_link_templates",true));
            if(isset($templates[$actpl]['content'])&&$templates[$actpl]['content']!='') $actpl = $templates[$actpl]['content'];

            $repeater = \WPDM\Package::fetchTemplate($actpl, get_the_ID(), 'link');

            if($repeater != '')
                $html .= "<div class='col-md-{$cols}'><div class='ap-block'>".$repeater."</div></div>";
        }

        if( $total == 0 ) $html = "<div class='col-md-12'>". __('No download found!','wpdm-archive-page')."</div>";

        echo str_replace( array("\r","\n"), "", "$html<div class='clear'></div>".$phtml."<div class='clear'></div>" );
        die();
    }

    /**
     * @param array $params
     * @return array|null|WP_Post
     * @usage Short-code callback function for [wpdm_search_page]
     */
    function SeachBar($params = array()){
        @extract($params);
        $dir = dirname(__FILE__);
        $url = WP_PLUGIN_URL . '/' . basename($dir);
        $extra_search = (isset($_GET['search'])) ? array_map_recursive('stripslashes',$_GET['search']) : array();
        $extra_search = array_map_recursive('esc_attr',$extra_search);
        $src = isset($_GET['q']) ? esc_attr($_GET['q']): '' ;
        $order_by = isset($extra_search['order_by']) ? $extra_search['order_by'] : '';
        $order = isset($extra_search['order']) ? : '';

        $link_template = isset($link_template) ? $link_template : '';
        $cols = ( isset($cols) && ( $cols > 0 ) ) ? $cols : 1;
        $cols = 'col-md-'.intval( 12 / $cols );

        ob_start();
        include wpdm_tpl_path("advanced-search-form.php", __DIR__.'/tpls/');
        $search_form = ob_get_clean();

        ob_start();
        include wpdm_tpl_path("advanced-search-result.php", __DIR__.'/tpls/');
        $search_result = ob_get_clean();

        if( !isset($position) || $position == '' || $position == 'top')
            return "<div class='w3eden'>{$search_form}{$search_result}</div>";

        if($position == 'left')
            return "<div class='w3eden'><div class='row'><div class='col-md-4 col-full-inner'>{$search_form}</div><div class='col-md-8'>{$search_result}</div></div></div>";

        if($position == 'right')
            return "<div class='w3eden'><div class='row'><div class='col-md-8'>{$search_result}</div><div class='col-md-4 col-full-inner'>{$search_form}</div></div></div>";
    }

    /**
     * @param array $params
     * @return array|null|WP_Post
     * @usage Shortcode callback function for [wpdm_simple_search]
     */
    function simpleSeachBar( $params = array() ){
        global $wpdb;
        @extract($params);
        $link_template = isset($template) ? $template : 'link-template-calltoaction3';
        $items_per_page = isset($items_per_page) ? $items_per_page : 0;
        $init = isset($init) ? $init : 0;
        update_post_meta(get_the_ID(), "__wpdm_ac_params", $params);
        update_post_meta(get_the_ID(), "__wpdm_link_template", $link_template);
        update_post_meta(get_the_ID(), "__wpdm_items_per_page", $items_per_page);
        update_post_meta(get_the_ID(), "__wpdm_ssr_init", $init);

        ob_start();
        include wpdm_tpl_path("simple-search-form.php", __DIR__.'/tpls/');
        $html = ob_get_clean();

        return str_replace(array("\r","\n"),"",$html);
    }

    /**
     * @return null
     * @usage Adds Archive Page settings in WPDM Settings -> Basic Tab
     */

    function LinkTemplateOption(){
        ?>
        <tr>
            <td><?php _e('Link Template for Archive Page', 'wpdm-archive-page'); ?></td>
            <td>
                <select name="_wpdm_ap_search_page_template" id="ltac">
                    <?php
                    $actpl = get_option("_wpdm_ap_search_page_template", 'link-template-default.php');
                    $ctpls = scandir(WPDM_BASE_DIR.'/tpls/link-templates/');
                    array_shift($ctpls);
                    array_shift($ctpls);
                    foreach($ctpls as $ctpl){
                        $tmpdata = file_get_contents(WPDM_BASE_DIR.'/tpls/link-templates/'.$ctpl);
                        if( preg_match( "/WPDM[\s]+Link[\s]+Template[\s]*:([^\-\->]+)/", $tmpdata, $matches ) ){
                            ?>
                            <option value="<?php echo $ctpl; ?>" <?php echo ( $actpl == $ctpl ) ? ' selected ' : ''; ?>><?php echo $matches[1]; ?></option>
                            <?php
                        }
                    }

                    $templates = maybe_unserialize(get_option("_fm_link_templates",true));
                    if(is_array($templates)) {
                        foreach ($templates as $id => $template) {
                            ?>
                            <option value="<?php echo $id; ?>" <?php echo ($actpl == $id) ? ' selected ' : ''; ?>><?php echo $template['title']; ?></option>
                        <?php }
                    }
                    ?>
                </select>
            </td>
        </tr>
        <?php
    }

    /**
     * @usage Get Child Categories
     */
    function GetChildCats(){
        if(isset($_REQUEST['wpdmtask']) && $_REQUEST['wpdmtask'] == 'wpdm_ap_get_child_cats'){

            $bu = get_terms('wpdmcategory', array('hide_empty'=> false, 'parent'=>(int)$_REQUEST['parent']));

            echo (count($bu) > 0) ? '<option value="">Select</option>' : '<option value="">Nothing here</option>';

            foreach($bu as $term){
                echo "<option value='{$term->term_id}'>{$term->name}</option>";
            }

            die();
        }
    }

    /**
     * @usage Callback function for short-code [wpdm_tags]
     * @param array $params
     * @return string
     */
    function Tags($params = array()){
        global $wpdb;
        @extract($params);
        $parent = isset($parent)?$parent:0;
        $args = array(
            'orderby'       => 'name',
            'order'         => 'ASC',
            'hide_empty'    => false,
            'exclude'       => array(),
            'exclude_tree'  => array(),
            'include'       => array(),
            'number'        => '',
            'fields'        => 'all',
            'slug'          => '',
            'parent'         => $parent,
            'hierarchical'  => true,
            'child_of'      => 0,
            'get'           => '',
            'name__like'    => '',
            'pad_counts'    => false,
            'offset'        => '',
            'search'        => '',
            'cache_domain'  => 'core'
        );
        $categories = get_terms('post_tag',$args);
        $pluginsurl = plugins_url();
        $cols = isset($cols)&&$cols>0?$cols:2;
        $scols = intval(12/$cols);
        $icon = isset($icon)?"<i class='fa fa-{$icon}'></i>":"<i class='fa fa-tag'></i>";
        $btnstyle = isset($btnstyle)?$btnstyle:'success';
        $k = 0;
        $html = "<div  class='wpdm-all-categories wpdm-categories-{$cols}col'><ul class='row'>";
        foreach($categories as $id=>$category){
            $catlink = get_term_link($category);
            if($category->parent==$parent) {
                $ccount = $category->count;
                if(isset($showcount)&&$showcount) $count  = "&nbsp;<span class='wpdm-count'>($ccount)</span>";
                $html .= "<div class='col-md-{$scols} col-tag'><a class='btn btn-{$btnstyle} btn-block text-left' href='$catlink' >{$icon} &nbsp; ".htmlspecialchars(stripslashes($category->name))."</a></div>";
                $k++;
            }
        }

        $html .= "</ul><div style='clear:both'></div></div><style>.col-tag{ margin-bottom: 10px !important; } .col-tag .btn{ text-align: left !important; padding-left: 10px !important; box-shadow: none !important; }</style>";
        if($k==0) $html = '';
        return "<div class='w3eden'>".str_replace(array("\r","\n"),"",$html)."</div>";
    }



        /**
     * @param $id
     * @param bool|false $taxonomy
     * @param bool|false $link
     * @param string $separator
     * @param bool|false $nicename
     * @param array $visited
     * @return array|mixed|null|object|string|WP_Error
     */
    function GetCustomCategoryParents( $id, $taxonomy = false, $link = false, $separator = '/', $nicename = false, $visited = array() ) {

        if(!($taxonomy && is_taxonomy_hierarchical( $taxonomy )))
            return '';

        $chain = '';
        // $parent = get_category( $id );
        $parent = get_term( $id, $taxonomy);
        if ( is_wp_error( $parent ) )
            return $parent;

        if ( $nicename )
            $name = $parent->slug;
        else
            $name = $parent->name;

        if ( $parent->parent && ( $parent->parent != $parent->term_id ) && !in_array( $parent->parent, $visited ) ) {
            $visited[] = $parent->parent;
            // $chain .= get_category_parents( $parent->parent, $link, $separator, $nicename, $visited );
            $chain .= $this->GetCustomCategoryParents( $parent->parent, $taxonomy, $link, $separator, $nicename, $visited );
        }

        if ( $link ) {
            // $chain .= '<a href="' . esc_url( get_category_link( $parent->term_id ) ) . '" title="' . esc_attr( sprintf( __( "View all posts in %s" ), $parent->name ) ) . '">'.$name.'</a>' . $separator;
            $chain .= '<a href="' . esc_url( get_term_link( (int) $parent->term_id, $taxonomy ) ) . '" title="' . esc_attr( sprintf( __( "View all posts in %s","wpdm-archive-page" ), $parent->name ) ) . '">'.$name.'</a>' . $separator;
        } else {
            $chain .= $name.$separator;
        }
        return $chain;
    }

    /**
     * @usage Save Parent ID of Current Category
     */
    function ChangeCatParent(){
        $cat_id = isset($_REQUEST['cat_id']) ? (int) $_REQUEST['cat_id'] : '';
        $result['type'] = 'failed';
        if(is_numeric($cat_id)) {
            $result['type'] = 'success';

            $parents = rtrim($this->GetCustomCategoryParents($cat_id,'wpdmcategory',false,'>',false),'>');
            $temp = explode('>', $parents);
            //print_r($temp);
            $count = count($temp);
            $str = "";
            for($i = 1; $i<=$count ; $i++){
                if($i == $count) {
                    $str .= "{$temp[$i-1]}";
                }
                else {

                    $parent = get_term_by('name', $temp[$i-1], 'wpdmcategory');
                    //print_r($parent);
                    $link = get_term_link($parent);
                    //print_r($link);
                    $a = "<a class='wpdm-cat-link2' rel='{$parent->term_id}' test_rel='{$parent->term_id}' title='{$parent->description}' href='$link'>{$parent->name}</a> <i class=\"fas fa-caret-right\"></i> ";
                    $str .= $a;
                }
            }
            $result['parent'] = $str;

        }

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $result = json_encode($result);
            echo $result;
        }
        else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }

        die();
    }

    /**
     * @usage Add short-code generator function with tinymce button add-on
     */
    function MCEButtonHelper(){
        ?>
        <style>#apc_chosen{ width: 100% !important; } #plnk_tpl_ap_chosen{ width: 140px !important; } #apms_chosen{ width: 200px !important; }</style>
        <div class="panel panel-default">
            <div class="panel-heading">Archive Page</div>
            <div class="panel-body">
                <div style="display: inline-block;width: 250px">
                <?php wpdm_dropdown_categories('c',0, 'apc'); ?>
                </div>
                <select style="margin-left: 5px;" id="catvw_ap">
                    <option value="extended">Cat. View:</option>
                    <option value="hidden">Hidden</option>
                    <option value="compact">Compact</option>
                    <option value="extended">Extended</option>
                    <option value="sidebar">Sidebar</option>
                </select>
                <select style="margin-right: 5px;" id="btns_ap">
                    <option value="default">Button:</option>
                    <option value="default">Default</option>
                    <option value="success">Success</option>
                    <option value="primary">Primary</option>
                    <option value="warning">Warning</option>
                    <option value="danger">Danger</option>
                    <option value="info">Info</option>
                    <option value="inverse">Inverse</option>
                </select>
                <div style="clear: both;margin-bottom: 5px"></div>

                <?php echo \WPDM\admin\menus\Templates::Dropdown(array('id'=>'plnk_tpl_ap')); ?>
                <select id="acob" style="margin-right: 5px;width: 100px">
                    <option value="post_title">Order By:</option>
                    <option value="post_title">Title</option>
                    <option value="download_count">Downloads</option>
                    <option value="package_size_b">Package Size</option>
                    <option value="view_count">Views</option>
                    <option value="date">Publish Date</option>
                    <option value="modified">Update Date</option>
                </select><select id="acobs" style="margin-right: 5px">
                    <option value="asc">Order:</option>
                    <option value="asc">Asc</option>
                    <option value="desc">Desc</option>
                </select>
                <button class="btn btn-primary" id="acps">Insert to Post</button>
                <script>
                    jQuery('#acps').click(function(){

                        var cats = jQuery('#apc').val()!='-1'?' category="' + jQuery('#apc').val() + '" ':'';
                        var bts = ' button_style="' + jQuery('#btns_ap').val() + '" ';
                        var catvw = ' cat_view="' + jQuery('#catvw_ap').val() + '" ';
                        var linkt = ' link_template="' + jQuery('#plnk_tpl_ap').val() + '" ';
                        var acob = ' order_by="' + jQuery('#acob').val() + '" order="' + jQuery('#acobs').val() + '"';
                        var win = window.dialogArguments || opener || parent || top;
                        win.send_to_editor('[wpdm-archive' + cats + catvw + bts + linkt + acob + ' items_per_page="10"]');
                        tinyMCEPopup.close();
                        return false;
                    });
                </script>
            </div>
            <div class="panel-heading">Categories</div>
            <div class="panel-body">
                <select id="spc" style="margin-right: 5px">
                    <option value="1">Package Count:</option>
                    <option value="1">Show</option>
                    <option value="0">Hide</option>
                </select><select id="ssc" style="margin-right: 5px">
                    <option value="1">Sub Cats:</option>
                    <option value="1">Show</option>
                    <option value="0">Hide</option>
                </select><select id="apcols" style="margin-right: 5px">
                    <option value="3">Cols:</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                </select>
                <button class="btn btn-primary" id="apcts">Insert to Post</button>
                <script>
                    jQuery('#apcts').click(function(){

                        var scats =' subcat="' + jQuery('#ssc').val() + '" ';
                        var count = ' showcount="' + jQuery('#spc').val() + '" ';
                        var cols = ' cols="' + jQuery('#apcols').val() + '" ';
                        var win = window.dialogArguments || opener || parent || top;
                        win.send_to_editor('[wpdm-categories' + scats + count + cols + ']');
                        tinyMCEPopup.close();
                        return false;
                    });
                </script>
            </div>
            <div class="panel-heading">More...</div>
            <div class="panel-body">
                <select id="apms" style="margin-right: 5px">
                    <option value="" disabled="disabled" selected="selected">More Shortcodes...</option>
                    <option value='[wpdm-tags cols="4" icon="tag"  btnstyle="default"]'>Tags</option>
                    <option value='[wpdm_search_page cols="1" items_per_page="10" link_template="link-template-calltoaction4" position="top"]'>Advanced Search ( Top )</option>
                    <option value='[wpdm_search_page cols="1" items_per_page="10" link_template="link-template-calltoaction4" position="left"]'>Advanced Search ( Left )</option>
                    <option value='[wpdm_search_page cols="1" items_per_page="10" link_template="link-template-calltoaction4" position="right"]'>Advanced Search ( Right )</option>
                </select>
                <button class="btn btn-primary" id="apmsb">Insert to Post</button>
                <script>
                    jQuery('#apmsb').click(function(){
                        var win = window.dialogArguments || opener || parent || top;
                        win.send_to_editor(jQuery('#apms').val());
                        tinyMCEPopup.close();
                        return false;
                    });
                </script>
            </div>
        </div>

        <?php
    }

    /**
     * @usage Add Styles and Scripts in WP Head
     */
    function WPHead(){
        global $post;

        if( is_object($post) ){
            if (
                !strpos($post->post_content, 'wpdm-archive') &&
                !strpos($post->post_content,'wpdm_archive') &&
                !strpos($post->post_content,'wpdm-categories') &&
                !strpos($post->post_content,'wpdm_categories') &&
                !strpos($post->post_content,'wpdm-tags') &&
                !strpos($post->post_content,'wpdm_tags') &&
                !strpos($post->post_content,'wpdm-search-page') &&
                !strpos($post->post_content,'wpdm_search_page') &&
                !strpos($post->post_content,'wpdm_simple_search')
            )
                return;
        }
        ?>
<style type="text/css">
    .w3eden .bootstrap-select span.filter-option{ background: transparent !important; }
    .w3eden .wpdm-all-categories div.cat-div{ margin-bottom: 10px; }
    .w3eden .wpdm-all-categories a.wpdm-pcat{ font-weight: 800; }
    .w3eden .wpdm-all-categories a.wpdm-scat{
        font-weight: 400;
        font-size: 9pt;
        margin-right: 10px;
        opacity: 0.6;
    }
    .w3eden .wpdm-categories ul li,
    .w3eden .wpdm-downloads ul li{
        list-style: none !important;
        list-style-type: none !important;
    }
    .w3eden .wpdm-categories ul:not(.row){
        list-style: none!important;
        padding: 0px !important;
        margin: 0px !important;
    }
    .w3eden .wpdm-categories ul.row .col-md-4{
        padding-bottom: 15px;
        position: relative;
    }
    .w3eden .wpdm-categories ul.row .col-md-4 > .dropdown-menu{
        left: 15px;
        margin-top: -15px !important;
        width: calc(100% - 30px);
    }

    .w3eden .wpdm-count{ font-size:12px; color:#888; }
    .w3eden .input-src .btn{
        background: #fff !important;color:#1393dc !important;border:1px solid #cfcfcf;font-size:10px;padding;padding: 0 16px;line-height: 0;border-left: 0 !important;margin-left: -2px !important;z-index: 9;
    }
    .w3eden .input-src #src:focus + .input-group-append .btn{
        border-color: #66afe9 !important;
    }
    #wpdm-downloads *{ font-size: 10pt; }
    #wpdm-downloads .wpdm-loading{
        margin-left: 20px;
    }

    .w3eden .btn-ddm{
        position: absolute;right: 4px;border:0 !important;
        -webkit-border-top-left-radius: 0px !important;
        -webkit-border-bottom-left-radius: 0px !important;
        -moz-border-radius-topleft: 0px !important;
        -moz-border-radius-bottomleft: 0px !important;
        border-top-left-radius: 0px !important;
        border-bottom-left-radius: 0px !important;
        background:rgba(0,0,0,0.2) !important;
        color: #ffffff !important;
        height: calc(100% - 8px);
    }
    .w3eden .panel-footer img{
        max-height: 30px;
    }

    #srcp .input-group{
        width: 100%;
    }
    #srcp .input-group .input-group-addon{
        background: #ffffff;
        border-radius: 3px 0 0 3px;
    }
    #srcp .input-group .form-control{
        border-radius: 0 3px 3px 0;
    }

    .wpdm-cat-dropdown .wpdm-cat-item.dropdown-submenu{
        position: relative;
    }
    .wpdm-cat-dropdown .wpdm-cat-item.dropdown-submenu:hover > .dropdown-menu{
        display: block;
        top: 0;
        left: 80%;
    }
    .text-left{
        text-align: left !important;
    }

    ul.dropdown-menu{
        border: 0;
    }

    .dropdown-menu .wpdm-cat-link.text-left {
        line-height: 32px;
    }
    ul.wpdm-cat-dropdown.row {
        margin-bottom: 0;
    }

</style>
    <?php if( is_object($post) && !strpos($post->post_content,'wpdm-archive') && !strpos($post->post_content,'wpdm_archive')) return; ?>
<script>
    function htmlEncode(value){
        return jQuery('<div/>').text(value).html();
    }

    jQuery(function($){

        jQuery('body').on('click', '.pagination a',function(e){
            e.preventDefault();
            jQuery('#wpdm-downloads').prepend('<div class="wpdm-loading"><i class="fas fa-sun fa-spin"></i> <?php _e('Loading',''); ?>...</div>').load(this.href);
        });

        jQuery('.wpdm-cat-link').click(function(e){
            e.preventDefault();
            jQuery('.wpdm-cat-link').removeClass('active');
            jQuery(this).addClass('active');

            var cat_id = jQuery(this).attr('rel');
            jQuery.ajax({
                type : "post",
                dataType : "json",
                url : '<?php echo admin_url('admin-ajax.php'); ?>',
                data : {action: "wpdm_change_cat_parent", cat_id : cat_id},
                success: function(response) {
                    console.log(response);
                    if(response.type == "success") {
                        if(jQuery('#src').val() !='')
                        jQuery('#inp').html('<?php _e('Search Result For','wpdm-archive-page'); ?> <b>'+htmlEncode(jQuery('#src').val())+'</b> <?php _e('in category',''); ?> <b>'+response.parent+'</b>');
                        else
                        jQuery('#inp').html(response.parent);
                    }
                }
            });

            jQuery('#wpdm-downloads').prepend('<div class="wpdm-loading"><i class="fas fa-sun fa-spin"></i>  <?php _e('Loading','wpdm-archive-page'); ?>...</div>').load('<?php echo home_url('/?wpdmtask=get_downloads&pg='.get_the_ID());?>&category='+this.rel + '&search='+jQuery('#src').val()+'&order_by=' + jQuery('#order_by').val() +'&order=' + jQuery('#order').val());

            $('.dropdown-menu').fadeOut();



        });

        jQuery('#wpdm-cats-compact').on('change',function(e){

            var cat_id = jQuery(this).val();
            if(cat_id == -1) cat_id = 0;
            jQuery('#initc').val(cat_id);
            jQuery.ajax({
                type : "post",
                dataType : "json",
                url : '<?php echo admin_url('admin-ajax.php'); ?>',
                data : {action: "wpdm_change_cat_parent", cat_id : cat_id},
                success: function(response) {
                    console.log(response);
                    if(response.type == "success") {
                        jQuery('#inp').html(response.parent);
                    }
                }
            });
            var keyword = jQuery('#src').val() != ''?'&search='+jQuery('#src').val():'';
            jQuery('#wpdm-downloads').prepend('<div class="wpdm-loading"><i class="fas fa-sun fa-spin"></i>  <?php _e('Loading','wpdm-archive-page'); ?>...</div>').load('<?php echo home_url('/?wpdmtask=get_downloads&pg='.get_the_ID());?>&category='+ cat_id + keyword + '&order_by=' + jQuery('#order_by').val() +'&order=' + jQuery('#order').val());

        });

        jQuery('body').on('click', '.wpdm-cat-link2', function(e){

            e.preventDefault();
            jQuery('.wpdm-cat-link').removeClass('active');
            var new_rel = jQuery(this).attr('test_rel');
            if( new_rel !== 'undefined') {
                jQuery('a[rel=' + new_rel + ']').addClass('active');
            }

            var cat_id = jQuery(this).attr('rel');
            jQuery.ajax({
                type : "post",
                dataType : "json",
                url : '<?php echo admin_url('admin-ajax.php'); ?>',
                data : {action: "wpdm_change_cat_parent", cat_id : cat_id},
                success: function(response) {
                    console.log(response);
                    if(response.type == "success") {
                        jQuery('#inp').html(response.parent)
                    }
                }
            });


            jQuery('#wpdm-downloads').prepend('<div class="wpdm-loading"><i class="fas fa-sun fa-spin"></i>  <?php _e('Loading','wpdm-archive-page'); ?>...</div>').load('<?php echo home_url('/?wpdmtask=get_downloads&pg='.get_the_ID());?>&category='+this.rel + '&order_by=' + jQuery('#order_by').val() +'&order=' + jQuery('#order').val());

        });

        jQuery('#order_by, #order').on('change',function(){
            jQuery('#wpdm-downloads').prepend('<div class="wpdm-loading"><i class="fas fa-sun fa-spin"></i>  <?php _e('Loading','wpdm-archive-page'); ?>...</div>').load('<?php echo home_url('/?wpdmtask=get_downloads&pg='.get_the_ID());?>&category='+jQuery('#initc').val() + '&search=' + encodeURIComponent(jQuery('#src').val() ) + '&order_by=' + jQuery('#order_by').val() +'&order=' + jQuery('#order').val());
        });


        jQuery('#srcp').submit(function(e){
            e.preventDefault();
            jQuery('.wpdm-cat-link').removeClass('active');

            jQuery('#inp').html('<?php _e('Search Result For','wpdm-archive-page'); ?> <b>'+htmlEncode(jQuery('#src').val())+'</b>');
            jQuery('#wpdm-downloads').prepend('<div class="wpdm-loading"><i class="fas fa-sun fa-spin"></i>  <?php _e('Loading','wpdm-archive-page'); ?>...</div>').load('<?php echo home_url('/?wpdmtask=get_downloads&pg='.get_the_ID().'&search=');?>'+encodeURIComponent(jQuery('#src').val() )+'&category='+jQuery('#initc').val() + '&order_by=' + jQuery('#order_by').val() +'&order=' + jQuery('#order').val() );

        });
        jQuery('#wpdm-archive-page-home').click(function(e){
            e.preventDefault();
            jQuery('.wpdm-cat-link').removeClass('active');
            jQuery('#inp').html('All Downloads');
            jQuery('#wpdm-downloads').prepend('<div class="wpdm-loading"><i class="fas fa-sun fa-spin"></i> <?php _e('Loading','wpdm-archive-page'); ?>...</div>').load('<?php echo home_url('/?wpdmtask=get_downloads&pg='.get_the_ID().'&search=');?>'+encodeURIComponent(jQuery('#src').val()) +'&category='+jQuery('#initc').val() + '&order_by=' + jQuery('#order_by').val() +'&order=' + jQuery('#order').val());
        });





        $('#wpdm-downloads').prepend('<div class="wpdm-loading"><i class="fas fa-sun fa-spin"></i> <?php _e('Loading','wpdm-archive-page'); ?>...</div>').load('<?php echo home_url('/?wpdmtask=get_downloads&pg='.get_the_ID());?>&category='+encodeURIComponent(jQuery('#initc').val()));

    });

</script>
<?php
}



}


if(!class_exists('WPDM_SearchWidget')){

    class WPDM_SearchWidget extends \WP_Widget {

        function __construct() {
            parent::__construct(false, 'WPDM Search');
        }

        function widget($args, $instance) {
            extract( $args );
            $title = apply_filters('widget_title', $instance['title']);
            $url = get_permalink($instance['rpage']);

            echo isset( $before_widget ) ? $before_widget : "";
            if ( $title ) echo isset($before_title) ? $before_title : "" . $title . isset( $after_title ) ? $after_title : "";
            echo "<div class='w3eden'><form action='".$url."' class='wpdm-pro'>";
            echo "<div class='input-group'><input type=text class='form-control' name='q' /><span class='input-group-btn'><button class='btn btn-default'><i class='fas fa-search'></i> ".__("Search","wpdm-archive-page")."</button></span></div><div class='clear'></div>";
            echo "</form></div>";
            echo isset( $after_widget ) ? $after_widget : "";
        }

        function update($new_instance, $old_instance) {
            $instance = $old_instance;
            $instance['title'] = strip_tags($new_instance['title']);
            $instance['rpage'] = strip_tags($new_instance['rpage']);
            return $instance;
        }

        function form($instance) {
            $title = isset($instance['title']) ? esc_attr($instance['title']) : "";
            $rpage = isset($instance['rpage']) ? esc_attr($instance['rpage']) : "";
            ?>
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
            </p>
            <p>
                <?php echo __("Search Result Page","wpdm-archive-page").":<br/>".wp_dropdown_pages("selected={$rpage}&echo=0&name=".$this->get_field_name('rpage'));  ?>
            </p>
            <div style="border:1px solid #ccc;padding:15px;margin-bottom: 15px;font-size:8pt">
                <?php _e("Note: Create a page with short-code <code>[wpdm_search_page]</code> and select that page as search redult page", "wpdm-archive-page");?>
            </div>
        <?php
        }
    }
}

if(!function_exists('array_map_recursive')) {
    function array_map_recursive($callback, $value){
        if (is_array($value)) {
            return array_map(function($value) use ($callback) { return array_map_recursive($callback, $value); }, $value);
        }
        return $callback($value);
    }
}

$wpdm_archive_page = new WPDM_ArchivePage();