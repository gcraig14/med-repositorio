<?php $term = get_term((int)$_POST['cid'], 'wpdmcategory'); ?>
<div class="breadcrumb" style="border-radius:0;">
    <?php \WPDM\libs\CategoryHandler::CategoryBreadcrumb((int)$_POST['cid'],0); ?>
</div>

<!--<h2 style="margin: 0 0 10px 0">--><?php //echo $term->name; ?><!--</h2>-->

<div class="list-group">
<?php

$terms = get_terms('wpdmcategory', array('parent'=>(int)$_POST['cid']));

foreach($terms as $term){
    echo "<a class='list-group-item apc-item-".esc_attr(strip_tags($_REQUEST['xid']))."' href='#' data-item-id='{$term->term_id}'>{$term->name}</a>";
}
?>
</div>

<table class="table table-border table-striped">

    <?php
    global $post;
    $cparams['posts_per_page'] = -1;
    $cparams['post_type'] = 'wpdmpro';
    $cparams['tax_query'] = array(array(
        'taxonomy' => 'wpdmcategory',
        'field' => 'term_id',
        'include_children' => false,
        'terms' => array($_POST['cid'])
    ));

    //order parameter
    $order = isset($_REQUEST['order']) ? addslashes(esc_attr($_REQUEST['order'])) : 'desc';
    $order_by = isset($_REQUEST['order_by']) ? addslashes(esc_attr($_REQUEST['order_by'])) : 'date';

    if($order_by !== '') {
        //order parameter
        if($order_by == 'view_count' || $order_by == 'download_count' || $order_by == 'package_size_b'){
            $cparams['meta_key'] = '__wpdm_' . $order_by;
            $cparams['orderby'] = 'meta_value_num';
        }
        else {
            $cparams['orderby'] = $order_by;
        }
        if($order == '') $order = 'ASC';
        $cparams['order'] = $order;

    }

    $packs = new WP_Query($cparams);

    while( $packs->have_posts() ){
        $packs->the_post();

       if( !wpdm_user_has_access( get_the_ID() ) ) continue;

        $icon = get_post_meta( get_the_ID(), '__wpdm_icon', true );
        $icon = ( $icon == '' ) ? WPDM_BASE_URL.'assets/file-type-icons/download4.png' : $icon;
        if(strpos($icon, 'file-type-icons/') && !strpos($icon, 'assets/file-type-icons/')) $icon = str_replace('file-type-icons/', 'assets/file-type-icons/', $icon);
            ?>
            <tr>
                <td><img src="<?php echo $icon; ?>" style="float: left;margin-right: 10px;width: 20px;" /> <?php the_title(); ?></td>
                <td><?php echo get_the_modified_date(); ?></td>
                <td class="text-right"><a href="#" class="btn btn-xs btn-apc-sidebar btn-primary apc-pack-<?php echo esc_attr(strip_tags($_REQUEST['xid'])); ?>" data-item-id="<?php the_ID(); ?>"><?php _e('View Details', 'wpdm-archive-page'); ?></a></td>
            </tr>
        <?php
    }
    ?>
</table>