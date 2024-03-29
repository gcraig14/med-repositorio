<?php
global $current_user, $wpdb;
$user = get_userdata($current_user->ID);


?>

    <div id="edit-profile-form">

        <?php if(isset($_SESSION['member_error'])){ ?>
            <div class="alert alert-danger" data-title="<?php _e( "SAVE FAILED!" , "download-manager" );?>"><?php echo implode('<br/>',$_SESSION['member_error']); unset($_SESSION['member_error']); ?></div>
        <?php } ?>
        <?php if(isset($_SESSION['member_success'])){ ?>
            <div class="alert alert-success" data-title="<?php _e( "DONE!" , "download-manager" );?>"><?php echo $_SESSION['member_success']; unset($_SESSION['member_success']); ?></div>
        <?php } ?>



        <form method="post" id="edit_profile" name="contact_form" action="" class="form">
            <div class="card card-default dashboard-card">
            <div class="card-header"><?php _e( "Basic Profile" , "download-manager" ); ?></div>
                <div class="card-body">
            <div class="row">
                <div class="col-md-6"><label for="name"><?php _e( "Display name:" , "download-manager" );?> </label><input type="text" class="required form-control" value="<?php echo $user->display_name;?>" name="wpdm_profile[display_name]" id="name"></div>
                <div class="col-md-6"><label for="payment_account"><?php _e( "PayPal Email:" , "download-manager" );?></label><input type="text" value="<?php echo get_user_meta($user->ID,'payment_account',true); ?>" class="form-control" name="payment_account" id="payment_account"> </div>

                <div class="col-md-6"><label for="username"><?php _e( "Username:" , "download-manager" );?></label><input type="text" class="required form-control" value="<?php echo $user->user_login;?>" id="username" readonly="readonly"></div>
                <div class="col-md-6"><label for="email"><?php _e( "Email:" , "download-manager" );?></label><input type="text" class="required form-control" name="wpdm_profile[user_email]" value="<?php echo $user->user_email;?>" id="email" ></div>

                <div class="col-md-6"><label for="new_pass"><?php _e( "New Password:" , "download-manager" );?> </label><input autocomplete="off" placeholder="<?php _e( "Use nothing if you don\'t want to change old password" , "download-manager" );?>" type="password" class="form-control" value="" name="password" id="new_pass"> </div>
                <div class="col-md-6"><label for="re_new_pass"><?php _e( "Re-type New Password:" , "download-manager" );?> </label><input autocomplete="off" type="password" value="" class="form-control" name="cpassword" id="re_new_pass"> </div>


                <?php do_action('wpdm_update_profile_filed_html', $user); ?>


                <div class="col-md-12 wpdm-clear"><label for="message"><?php _e( "Description:" , "download-manager" );?></label><textarea class="text form-control" cols="40" rows="8" name="wpdm_profile[description]" id="message"><?php echo htmlspecialchars(stripslashes($current_user->description));?></textarea></div>


            </div>
            </div>
            </div>

            <?php do_action("wpdm_edit_profile_form"); ?>

            <div class="row">
                <div class="col-md-12 wpdm-clear"><button type="submit" style="width: 200px" class="btn btn-lg btn-primary" id="edit_profile_sbtn"><i class="fas fa-hdd"></i> &nbsp;<?php _e( "Save Changes" , "download-manager" );?></button></div>
            </div>


        </form>
        <div id="edit-profile-msg">
        </div>
    </div>

<script>
    jQuery(function ($) {
        $('#edit_profile').on('submit', function (e) {
            e.preventDefault();
            var edit_profile_sbtn = $('#edit_profile_sbtn').html();
            $('#edit_profile_sbtn').html("<i class='fa fa-sync fa-spin'></i> Please Wait...").attr('disabled','disabled');
            $(this).ajaxSubmit({
                success: function (res) {

                    $('#edit-profile-msg').html("<div class='alert alert-"+res.type+"' data-title='"+res.title+"'>"+res.msg+"</div>");
                    $('#edit_profile_sbtn').html(edit_profile_sbtn).removeAttr('disabled');
                }
            });
        });
    });
</script>