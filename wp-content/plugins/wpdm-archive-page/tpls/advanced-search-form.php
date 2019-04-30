<script src='<?php echo $url . '/libs/daterangepicker/moment.min.js' ?>'></script>
<script src='<?php echo $url . '/libs/daterangepicker/daterangepicker.js' ?>'></script>
<link href='<?php echo $url . '/libs/daterangepicker/daterangepicker-bs3.css' ?>' rel="stylesheet">

<!-- Range Slider -->
<script src='<?php echo $url . '/libs/slider/js/bootstrap-slider.js' ?>'></script>
<link href='<?php echo $url . '/libs/slider/css/slider.css' ?>'  rel="stylesheet">

<!-- Bootstrap Multi Select -->
<script src='<?php echo $url . '/libs/bootstrap-select/bootstrap-select.min.js' ?>'></script>
<link rel="stylesheet" href='<?php echo $url . '/libs/bootstrap-select/bootstrap-select.min.css' ?>'>
<style>.col-full-inner .col-md-6{ width: 100%; clear: both; } .slider-horizontal{ min-width: 100%; }</style>

<form action="" class="well">
    <div class="row">
        <div class="form-group col-md-6">
            <label for="publish_date"><?php _e("Publish Date", "wpdm-archive-page");?></label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input type="text" name="search[publish_date]" id="publish_date" class="form-control" value="<?php if(isset($extra_search['publish_date']) && $extra_search['publish_date'] != '') { echo esc_attr($extra_search['publish_date']); } ?>" />
            </div>
        </div>
        <div class="form-group col-md-6">
            <label for="update_date"><?php _e("Last Updated", "wpdm-archive-page");?></label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input type="text" name="search[update_date]" id="update_date" class="form-control" value="<?php if(isset($extra_search['update_date']) && $extra_search['update_date'] != '') { echo esc_attr($extra_search['update_date']); } ?>" />
            </div>
        </div>

        <script type="text/javascript">
            jQuery(function($) {
                var update_date = false;
                $('#update_date').focus(function(){
                    if(update_date === false) {
                        $(this).daterangepicker({
                            format: 'YYYY-MM-DD',
                            ranges: {
                                '<?php _e("Today", "wpdm-archive-page");?>': [moment(), moment()],
                                '<?php _e("Yesterday", "wpdm-archive-page");?>': [moment().subtract('days', 1), moment().subtract('days', 1)],
                                '<?php _e("Last 7 Days", "wpdm-archive-page");?>': [moment().subtract('days', 6), moment()],
                                '<?php _e("Last 30 Days", "wpdm-archive-page");?>': [moment().subtract('days', 29), moment()],
                                '<?php _e("This Month", "wpdm-archive-page");?>': [moment().startOf('month'), moment().endOf('month')],
                                '<?php _e("Last Month", "wpdm-archive-page");?>': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
                            },
                            opens: 'left',
                            separator: ' to '
                            //startDate: moment().subtract('days', 29),
                            //endDate: moment()
                        });

                        update_date = true;
                    }
                });

                var publish_date = false;
                $('#publish_date').focus(function(){
                    if(publish_date === false) {
                        $(this).daterangepicker({
                            format: 'YYYY-MM-DD',
                            ranges: {
                                '<?php _e("Today", "wpdm-archive-page");?>': [moment(), moment()],
                                '<?php _e("Yesterday", "wpdm-archive-page");?>': [moment().subtract('days', 1), moment().subtract('days', 1)],
                                '<?php _e("Last 7 Days", "wpdm-archive-page");?>': [moment().subtract('days', 6), moment()],
                                '<?php _e("Last 30 Days", "wpdm-archive-page");?>': [moment().subtract('days', 29), moment()],
                                '<?php _e("This Month", "wpdm-archive-page");?>': [moment().startOf('month'), moment().endOf('month')],
                                '<?php _e("Last Month", "wpdm-archive-page");?>': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
                            },
                            opens: 'right',
                            separator: ' to '

                        });

                        publish_date = true;
                    }
                });

            });
        </script>

    </div>
    <div class="row">
        <div class="form-group col-md-6">
            <label for="view_count"><?php _e("View Count", "wpdm-archive-page");?> [ >= ]</label> <br>
            <input type="text" name="search[view_count]" id="view_count" class="slider" data-slider-min="0" <?php if(isset($extra_search['view_count']) && $extra_search['view_count'] != '') { echo "data-slider-value='{$extra_search['view_count']}' value='{$extra_search['view_count']}'"; } ?> />
        </div>
        <div class="form-group col-md-6">
            <label for="download_count"><?php _e("Download Count", "wpdm-archive-page");?> [ >= ]</label> <br>
            <input type="text" name="search[download_count]" id="download_count" class="slider" data-slider-min="0" <?php if(isset($extra_search['download_count']) && $extra_search['download_count'] != '') { echo "data-slider-value='{$extra_search['download_count']}' value='{$extra_search['download_count']}'"; } ?> />
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-12">
            <label for="package_size"><?php _e("Package Size In Bytes", "wpdm-archive-page");?> [ >= ]</label> <br>
            <input type="text" name="search[package_size]" id="package_size" data-slider-min="0" data-slider-max="10000" class="slider col-md-12" <?php if(isset($extra_search['package_size']) && $extra_search['package_size'] != '') { echo "data-slider-value='{$extra_search['package_size']}' value='{$extra_search['package_size']}'"; } ?> />
        </div>
    </div>
    <script>
        jQuery(function($){
            $('.slider').slider({max:1000});
        });
    </script>

    <div class="row">
        <div class="form-group col-md-12">
            <label for="category"><?php _e("Package Categories", "wpdm-archive-page");?></label>
            <?php
            $args = array(
                'orderby'       => 'name',
                'order'         => 'ASC',
                'hide_empty'    => true,
                'exclude'       => array(),
                'exclude_tree'  => array(),
                'include'       => array(),
                'number'        => '',
                'fields'        => 'all',
                'slug'          => '',
                'hierarchical'  => true,
                'child_of'      => 0,
                'get'           => '',
                'name__like'    => '',
                'pad_counts'    => false,
                'offset'        => '',
                'search'        => '',
                'cache_domain'  => 'core'
            );
            $terms = get_terms('wpdmcategory',$args);
            $options = "";
            if ( !empty( $terms ) && !is_wp_error( $terms ) ){
                foreach ( $terms as $term ) {
                    if(isset($extra_search['category']) && in_array($term->term_id, $extra_search['category']))
                        $selected ='selected="selected"';
                    else $selected = '';
                    $options .= "<option value='{$term->term_id}' $selected>" . $term->name . "</option>";
                }
            }
            ?>

            <select name='search[category][]' class="form-control selectpicker" multiple="multiple" id="category">
                <?php echo $options; ?>
            </select>
        </div>
        <script>
            jQuery(function($){
                //$('.selectpicker').selectpicker();
            });
        </script>
    </div>

    <div class="row">
        <div class="form-group col-md-6">
            <label for="order_by"><?php _e("Order By", "wpdm-archive-page");?></label> <br>
            <select name="search[order_by]" class="form-control selectpicker" id="order_by">
                <option value=""><?php _e("Select Order By", "wpdm-archive-page");?></option>
                <option <?php selected($order_by, 'title'); ?> value="title"><?php _e("Title", "wpdm-archive-page");?></option>
                <option <?php selected($order_by, '__wpdm_view_count'); ?> value="__wpdm_view_count"><?php _e("View Count", "wpdm-archive-page");?></option>
                <option <?php selected($order_by, '__wpdm_download_count'); ?> value="__wpdm_download_count"><?php _e("Download Count", "wpdm-archive-page");?></option>
                <option <?php selected($order_by, '__wpdm_package_size_b'); ?> value="__wpdm_package_size_b"><?php _e("Package Size", "wpdm-archive-page");?></option>
                <option <?php selected($order_by, 'date'); ?> value="date"><?php _e("Publish Date", "wpdm-archive-page");?></option>
                <option <?php selected($order_by, 'modified'); ?> value="modified"><?php _e("Last Updated", "wpdm-archive-page");?></option>
            </select>
        </div>

        <div class="form-group col-md-6">
            <label for="order"><?php _e("Order", "wpdm-archive-page");?></label> <br>
            <select name="search[order]" class="form-control selectpicker" id="order">
                <option <?php selected($order, 'ASC'); ?> value="ASC"><?php _e("Ascending Order", "wpdm-archive-page");?></option>
                <option <?php selected($order, 'DESC'); ?> value="DESC"><?php _e("Descending Order", "wpdm-archive-page");?></option>
            </select>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-md-12">
            <label for="package_size"><?php _e("Search By Keyword", "wpdm-archive-page");?></label>
            <input type="text" class="form-control" placeholder="<?php _e("Looking For", "wpdm-archive-page");?>..." name="q" id="s" value="<?php echo stripcslashes($src); ?>">
        </div>
    </div>
    <button class="btn btn-primary btn-large" type="submit"><?php _e("Search", "wpdm-archive-page");?></button>
    <button class="btn btn-warning btn-large" type="reset"><?php _e("Reset", "wpdm-archive-page");?></button>
</form>