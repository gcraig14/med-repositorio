<div class='w3eden'>
    <form id="srcp" style="margin-bottom:20px">
        <div class="input-group input-group-lg">
            <div class="input-group-addon input-group-prepend" style="width: 50px"><span class="input-group-text" id="spro"><i class="fa fa-search"></i></span></div>
            <input type="text" class="form-control input-lg" name="src" value="<?php echo wpdm_query_var('s', 'txt'); ?>" placeholder="<?php _e('Search Package','wpdm-archive-page'); ?>" id="src">
        </div>
    </form>
    <div style='clear: both;'>
        <div  class='wpdm-downloads row' id='wpdm-downloads-ss'></div>
    </div>
</div>
<script>
    function htmlEncode(value){
        return jQuery('<div/>').text(value).html();
    }

    jQuery(function ($) {

        $('#srcp').submit(function(e){
            e.preventDefault();
            $('.wpdm-cat-link').removeClass('active');
            $('#inp').html('<?php _e('Search Result For','wpdm-archive-page'); ?> <b>'+htmlEncode($('#src').val())+'</b>');
            $('#spro').html('<i class="fas fa-sun fa-spin color-danger"></i>');
            $('#wpdm-downloads-ss').load('<?php echo  home_url('/?wpdmtask=get_downloads&pg='.get_the_ID().'&search='); ?>'+encodeURIComponent($('#src').val() ), function () {
                $('#spro').html('<i class="fa fa-search"></i>');
            });
        });


        $('body').on('click', '.pagination a',function(e){
            e.preventDefault();
            $('#wpdm-downloads-ss').prepend('<div class="wpdm-loading"><i class="fa fa-spin fa-spinner icon icon-spin icon-spinner"></i> <?php _e('Loading','wpdm-archive-page'); ?>...</div>').load(this.href);
            return false;
        });

        if($('#src').val() !== '')
            $('#srcp').submit();
    })
</script>