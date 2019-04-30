<div class="w3eden">
    <div class="row">
        <div class="col-md-3">
            <div class="list-group apc-sidebar">
                <?php
                $xid = uniqid();
                $parent = 0;
                if(isset($params['category'])) {

                    $params['category'] = str_replace(' ','', $params['category']);
                    $cats = explode(',', $params['category']);
                    foreach ($cats as $cat) {
                        $term = get_term_by("id", $cat, "wpdmcategory");

                        // 1. Skip if current user does not have access to this category ( Manage role based category access from category settings page )
                        // 2. If following line is active, category roles access must be defined or it will not be listed
                        //if( ! wpdm_user_has_access( $term->term_id, 'category' ) ) continue;
                        if(!is_wp_error($term) && isset($term->term_id))
                            echo "<a class='list-group-item apc-item-{$xid} apc-cat-{$term->term_id}' href='#' data-item-id='{$term->term_id}'><i class=\"fas fa-folder\"></i>&nbsp;{$term->name}</a>";
                    }

                }else {

                    $terms = get_terms('wpdmcategory', array('hide_empty' => false,'parent' => $parent));

                    foreach ($terms as $term) {

                        // 1. Skip if current user does not have access to this category ( Manage role based category access from category settings page )
                        // 2. If following line is active, category roles access must be defined or it will not be listed
                        //if( ! wpdm_user_has_access( $term->term_id, 'category' ) ) continue;

                        echo "<a class='list-group-item apc-item-{$xid} apc-cat-{$term->term_id}' href='#' data-item-id='{$term->term_id}'><i class=\"fas fa-folder\"></i>&nbsp;{$term->name}</a>";
                    }
                }
                ?>
            </div>
        </div>
        <div class="col-md-9">
            <div class="wpdm-loading" id="wpdm-loading-<?php echo $xid; ?>" style="border-radius:0;display:none;right:15px;"><i class="fa fa-refresh fa-spin"></i> <?php _e('Loading...','wpdm-archive-page'); ?></div>
            <div id="ap-content-<?php echo $xid; ?>">


                <div class="breadcrumb fetfont" style="border-radius:0;">
                    <?php _e('Newest Items','wpdm-archive-page'); ?>
                </div>

                <table class="table table-border table-striped">

                    <?php
                    global $post;
                    $cparams['posts_per_page'] = $params['items_per_page'];
                    $cparams['post_type'] = 'wpdmpro';
                    if(isset($cats) && count($cats) > 0) {
                        $cparams['tax_query'] = array(array(
                            'taxonomy' => 'wpdmcategory',
                            'include_children' => false,
                            'field' => 'term_id',
                            'terms' => $cats
                        ));
                    }

                    //order parameter
                    $order_by = isset($params['order_by'])? $params['order_by'] : 'date';
                    $order    = isset($params['order'])? $params['order'] : 'desc';

                    if(isset($order_by) && $order_by != '') {
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
                    //wpdmprecho($cparams);
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
                            <td class="text-right"><a href="#" class="btn btn-xs btn-primary btn-apc-sidebar apc-pack-<?php echo $xid; ?>" data-item-id="<?php the_ID(); ?>"><?php _e('View Details', 'wpdm-archive-page'); ?></a></td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>

            </div>
        </div>
    </div>
</div>

<script>
    jQuery(function($){

        $('body').on('click', '.apc-item-<?php echo $xid; ?>', function(){
            $('#wpdm-loading-<?php echo $xid; ?>').fadeIn();
            $('.apc-item-<?php echo $xid; ?> i.fas').removeClass('fa-folder-open').addClass('fa-folder');
            $('.apc-cat-'+$(this).data('item-id')+' i.fas').removeClass('fa-folder').addClass('fa-folder-open');
            $('#ap-content-<?php echo $xid; ?>').load("<?php echo admin_url('admin-ajax.php'); ?>", {action:'load_ap_content', cid: $(this).data('item-id'), xid: '<?php echo $xid; ?>', order_by: '<?php echo $order_by; ?>', order: '<?php echo $order; ?>' }, function(){ $('#wpdm-loading-<?php echo $xid; ?>').fadeOut(); });
            return false;
        });

        $('body').on('click', '.breadcrumb .folder', function(){
            $('#wpdm-loading-<?php echo $xid; ?>').fadeIn();
            $('.apc-cat-<?php echo $xid; ?> i.fas').removeClass('fa-folder-open').addClass('fa-folder');
            $('.apc-cat-'+$(this).data('cat')+' i.fas').removeClass('fa-folder').addClass('fa-folder-open');
            $('#ap-content-<?php echo $xid; ?>').load("<?php echo admin_url('admin-ajax.php'); ?>", {action:'load_ap_content', cid: $(this).data('cat'), xid: '<?php echo $xid; ?>', order_by: '<?php echo $order_by; ?>', order: '<?php echo $order; ?>'}, function(){ $('#wpdm-loading-<?php echo $xid; ?>').fadeOut(); });
            return false;
        });

        $('body').on('click', '.apc-pack-<?php echo $xid; ?>', function(){
            $('#wpdm-loading-<?php echo $xid; ?>').fadeIn();
            $('#ap-content-<?php echo $xid; ?>').load("<?php echo admin_url('admin-ajax.php'); ?>", {action:'load_ap_content', pid: $(this).data('item-id'), pagetemplate: '<?php echo isset($params['page_template'])?$params['page_template']:''; ?>', xid: '<?php echo $xid; ?>'}, function(){ $('#wpdm-loading-<?php echo $xid; ?>').fadeOut(); });
            return false;
        });
    });
</script>
<style>

    .w3eden .breadcrumb a,
    .w3eden .breadcrumb i{
        line-height: 21px;
        margin: 0 2px;
    }
    .w3eden .list-group, .list-group-item .fas{
        margin-right: 5px;
    }
    .w3eden .list-group, .list-group-item{
        border-radius: 0 !important;
        position: relative;
    }
    .w3eden .table-border{
        border: 1px solid #dddddd;
    }
    .w3eden .table-border td img{
        box-shadow: none;
    }
    .w3eden .table-border td{
        padding: 10px 15px !important;
        vertical-align: middle !important;
    }
</style>