<div class="row">
    <?php

    if(isset($_GET['q']) || isset($params['onload'])) {

        global $wpdb, $current_user;

        $query_params = array();

        //date meta
        if ( isset($extra_search['publish_date']) && $extra_search['publish_date'] != '') {
            $publish_dates = explode(' to ', $extra_search['publish_date']);
            $query_params['date_query'][] = array(
                'column' => 'post_date_gmt',
                'after' => $publish_dates[0],
                'before' => $publish_dates[1],
                'inclusive' => true,
            );
        }

        if ( isset($extra_search['update_date']) && $extra_search['update_date'] != '') {
            $update_dates = explode(' to ', $extra_search['update_date']);
            $query_params['date_query'][] = array(
                'column' => 'post_modified_gmt',
                'after' => $update_dates[0],
                'before' => $update_dates[1],
                'inclusive' => true,
            );
        }

        if ( isset($extra_search['view_count']) && ( $extra_search['view_count'] != '' || $extra_search['view_count'] > 0 ) ) {
            $query_params['meta_query'][] = array(
                'key' => '__wpdm_view_count',
                'value' => (int) $extra_search['view_count'],
                'type'    => 'numeric',
                'compare' => '>='
            );
        }

        if ( isset($extra_search['download_count']) && ($extra_search['download_count'] != '' || $extra_search['download_count'] > 0 ) ) {
            $query_params['meta_query'][] = array(
                'key' => '__wpdm_download_count',
                'value' => (int) $extra_search['download_count'],
                'type'    => 'numeric',
                'compare' => '>='
            );
        }

        if ( isset($extra_search['package_size']) && ( $extra_search['package_size'] != '' || $extra_search['package_size'] > 0 ) ) {
            $query_params['meta_query'][] = array(
                'key' => '__wpdm_package_size_b',
                'value' => (int) $extra_search['package_size'],
                'type'    => 'numeric',
                'compare' => '>='
            );
        }

        //order parameter
        if ( isset($extra_search['order_by']) && $extra_search['order_by'] != '') {
            if ($extra_search['order_by'] != 'modified' && $extra_search['order_by'] != 'date' && $extra_search['order_by'] != 'title' ) {
                $query_params['meta_key'] = $extra_search['order_by'];
                $query_params['orderby'] = 'meta_value_num';
            } else {
                $query_params['orderby'] = $extra_search['order_by'];
            }

            $query_params['order'] = $extra_search['order'];
        }


        //category parameter
        if (isset($extra_search['category']) && !empty($extra_search['category'])) {
            $query_params['tax_query'][] = array(
                'taxonomy' => 'wpdmcategory',
                'field' => 'term_id',
                'terms' => $extra_search['category'],
                'operator' => 'IN',
                'include_children' => false
            );
        }

        //template select
        $pg = isset($_GET['pg']) ? (int)$_GET['pg'] : 0;
        $actpl = get_option('_wpdm_ap_search_page_template', 'link-template-default.php');

        if ($link_template != '') $actpl = $link_template;

        //post_type and pagination parameter
        $items_per_page = !isset($items_per_page) || $items_per_page <= 0 ? 10 : $items_per_page;
        $page = isset($_GET['cp']) ? (int)$_GET['cp'] : 1;
        $start = ($page - 1) * $items_per_page;
        $query_params["post_type"] = "wpdmpro";
        $query_params["posts_per_page"] = $items_per_page;
        $query_params["offset"] = $start;
        $query_params["post_status"] = "publish";


        //search parameter
        if ($src != '') {
            $query_params['s'] = $src;
            $query_params['meta_query'] = array(
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


        //echo '<pre>';print_r($query_params);echo "</pre>";

        $q = new WP_Query($query_params);

        //echo '<pre>';print_r($q->request);echo "</pre>";

        $total = $q->found_posts;

        //pagination
        $pages = ceil($total / $items_per_page);
        $pag = new \WPDM\libs\Pagination();
        $pag->changeClass('wpdm-ap-pag');
        $pag->items($total);
        $pag->limit($items_per_page);
        $pag->currentPage($page);
        $url = strpos($_SERVER['REQUEST_URI'], '?') ? $_SERVER['REQUEST_URI'] . '&' : $_SERVER['REQUEST_URI'] . '?';
        $url = preg_replace("/\&cp=[0-9]+/", "", $url);
        $pag->urlTemplate($url . "cp=[%PAGENO%]");

        $html = '';

        $role = @array_shift(array_keys($current_user->caps));

        while ($q->have_posts()) {
            $q->the_post();
            $package_role = maybe_unserialize(get_post_meta(get_the_ID(), '__wpdm_access', true));
            if (is_array($package_role) && !in_array('guest', $package_role) && !in_array($role, $package_role)) {
                continue;
            }

            $ext = "_blank";
            $data = wpdm_custom_data(get_the_ID());
            $data += get_post(get_the_ID(), ARRAY_A);

            $data['download_count'] = isset($data['download_count']) ? $data['download_count'] : 0;
            $data['ID'] = get_the_ID();
            $data['id'] = get_the_ID();

            $link_label = isset($data['link_label']) ? stripslashes($data['link_label']) : __('Download', 'wpdm-archive-page');

            $data['page_url'] = get_permalink(get_the_ID());


            $role = @array_shift( array_keys( $current_user->caps ) );
            $templates = maybe_unserialize(get_option("_fm_link_templates", true));


            $data['files'] = isset($data['files']) ? maybe_unserialize($data['files']) : array();


            if (isset($templates[$actpl]['content']) && $templates[$actpl]['content'] != '') $actpl = $templates[$actpl]['content'];

            $repeater = FetchTemplate($actpl, $data, 'link');

            $html .= "<div class='{$cols}'>" . $repeater . "</div>";

        }

        if ($total == 0) $html = __('No download found!','wpdm-archive-page');
        echo str_replace(array("\r", "\n"), "", "$html<div class='clear'></div>" . $pag->show() . "<div class='clear'></div>");
    }
    ?>
</div>