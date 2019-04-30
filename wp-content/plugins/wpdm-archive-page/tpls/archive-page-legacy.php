<div class="w3eden">

    <div class="row">
        <div class="col-md-12">
            <div class="wpdm-categories">
                <ul class="wpdm-cat-dropdown row">
                <?php $this->catDropdown($category, $button_style, $category, $showcount); ?>
                </ul>
            </div>
        </div>
        <div class="col-md-12">


                <form id="srcp" style="margin-bottom: 10px">
                    <div class="row">
                        <input type="hidden" name="category" id="initc" value="<?php echo $category; ?>" />
                        <span class="col-md-12 form-group">

        <div class="input-group input-group-lg input-src">
        <input type="text" class="form-control" name="src" placeholder="<?php echo __('Search','wpdm-archive-page'); ?>" id="src">
          <div class="input-group-append input-group-btn">
            <button class="btn" type="submit"><i class="fas fa-search"></i></button>
          </div>
        </div>
        </span>



                        <span class="col-md-6">
        <label for="order_by"><?php echo __('Order By:','wpdm-archive-page'); ?></label>
        <select name="order_by" id="order_by" class="form-control wpdm-custom-select">
        <option value="date"><?php echo __('Publish Date','wpdm-archive-page'); ?></option>
        <option value="title"><?php echo __('Title','wpdm-archive-page'); ?></option>
        <option value="modified"><?php echo __('Last Updated','wpdm-archive-page'); ?></option>
        <option value="view_count"><?php echo __('View Count','wpdm-archive-page'); ?></option>
        <option value="download_count"><?php echo __('Download Count','wpdm-archive-page'); ?></option>
        <option value="package_size_b"><?php echo __('Package Size','wpdm-archive-page'); ?></option>
        </select>
        </span>
                        <span class="col-md-6">
        <label for="order"><?php echo __('Order:','wpdm-archive-page'); ?></label>
        <select name="order" id="order" class="form-control wpdm-custom-select">
        <option value="DESC"><?php echo __('Descending Order','wpdm-archive-page'); ?></option>
        <option value="ASC"><?php echo __('Ascending Order','wpdm-archive-page'); ?></option>
        </select>
        </span>

                    </div><div class="clear"></div>
                </form>

                <div class="row">
                    <div class="col-md-12">
                        <div class="breadcrumb" style="font-size: 11px">
                            <a href="#" id="wpdm-archive-page-home"><?php echo __('Home','wpdm-archive-page'); ?></a> <i class="fas fa-caret-right"></i>
                            <span id="inp"><?php echo __('All Downloads','wpdm-archive-page'); ?></span>
                        </div>
                    </div>
                </div>


            <div class='wpdm-downloads row' id='wpdm-downloads'><div class="col-md-12"> <?php _e('Select category or search...','wpdm-archive-page'); ?></div></div>

        </div>
    </div>
</div>

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
    .btn-block .pull-right{
        z-index: 999999 !important;
    }
    .w3eden .wpdm-cat-tree,
    .w3eden .wpdm-cat-tree li{
        margin: 0;
        padding: 0;
    }
    .wpdm-cat-tree li{
        list-style: none;
    }

    .w3eden .wpdm-cat-tree .wpdm-cat-item{
        margin-bottom: 2px !important;
        list-style: none;
    }
    .w3eden .wpdm-cat-tree .wpdm-cat-item .btn{
        text-align: left !important;
    }
    .w3eden .wpdm-cat-tree .wpdm-cat-item a{
        border: 0;
        text-decoration: none;
        color: #ffffff;
        font-size: 11px;
    }
    .w3eden .wpdm-cat-item .wpdm-dropdown-menu{

        padding: 2px 0 0 15px !important;

    }

</style>
<script>
    jQuery(function ($) {
        $('a[data-toggle="collapse"]').on('click', function () {
            $(this).children('.fa').toggleClass('fa-chevron-down');
            $(this).children('.fa').toggleClass('fa-chevron-up');
            $(this).toggleClass('active');
            /*attr('aria-expanded') == true) */
        });
    });
</script>