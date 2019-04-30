<?php
/**
 * User: shahnuralam
 * Date: 24/12/18
 * Time: 3:20 AM
 */
if (!defined('ABSPATH')) die();

$cols = isset($params['cols'])?$params['cols']:3;
$grid = array(1 => 12, 2 => 6, 3 => 4, 4 => 3, 6 => 2);
$grid_class = "col-md-".(isset($grid[$cols])?$grid[$cols]:4);
$sec_id = isset($params['elid'])?$params['elid']:uniqid();
$button_color = isset($params['button_color'])?$params['button_color']:'blue';
$hover_color = isset($params['hover_color'])?$params['hover_color']:'blue';
$hover_color = in_array($hover_color, array('green', 'blue', 'purple', 'primary', 'success', 'warning', 'danger', 'info'))?"var(--color-{$hover_color})":$hover_color;
$button_color = in_array($button_color, array('green', 'blue', 'purple', 'primary', 'success', 'warning', 'danger', 'info'))?"var(--color-{$button_color})":$button_color;
?>
<div class="w3eden" id="category-blocks-<?php echo $sec_id; ?>">
    <div class="category-blocks">
        <div class="row">
        <?php


        foreach ($categories as $i => $category){

            $icon = \WPDM\libs\CategoryHandler::icon($category->term_id);
            $icon = $icon?$icon: plugins_url("images/default-cat-icon.svg", dirname(__FILE__));
        ?>

            <div class="<?php echo $grid_class; ?>">
                <a href="<?php echo get_term_link($category->term_id); ?>" class="panel panel-default panel-category">
                    <div class="panel-body text-center">


                        <img class="cat-icon" src="<?php echo $icon; ?>" alt="<?php echo $category->name; ?>" />
                        <h3 class="cat-name"><?php echo $category->name; ?></h3>
                        <div class="cat-info text-muted"><?php echo $category->count; ?> items</div>
                        <span class="btn btn-primary">Explore</span>

                    </div>
                </a>
            </div>


        <?php } ?>
        </div>
    </div>
</div>
<style>
    #category-blocks-<?php echo $sec_id; ?>.w3eden .cat-icon{ width: 48px; margin: 5px 15px 15px; }
    #category-blocks-<?php echo $sec_id; ?>.w3eden h3.cat-name{
        margin: 0 0 5px; font-weight: 700; font-size: 12pt; color: #555555;
        -webkit-transition: all 400ms ease-in-out;
        -moz-transition: all 400ms ease-in-out;
        -ms-transition: all 400ms ease-in-out;
        -o-transition: all 400ms ease-in-out;
        transition: all 400ms ease-in-out;
    }
    #category-blocks-<?php echo $sec_id; ?>.w3eden .cat-info{ margin-bottom: 10px; font-size: 12px; }
    #category-blocks-<?php echo $sec_id; ?>.w3eden .panel-category{
        padding: 20px;
        -webkit-transition: all 400ms ease-in-out;
        -moz-transition: all 400ms ease-in-out;
        -ms-transition: all 400ms ease-in-out;
        -o-transition: all 400ms ease-in-out;
        transition: all 400ms ease-in-out;
        display: block;
    }
    #category-blocks-<?php echo $sec_id; ?>.w3eden .panel-category:hover{
        border: 1px solid <?php echo $hover_color; ?> !important;
        text-decoration: none !important;
    }
    #category-blocks-<?php echo $sec_id; ?>.w3eden a:hover{
        text-decoration: none !important;
    }
    #category-blocks-<?php echo $sec_id; ?>.w3eden a:hover .cat-name{
        color: <?php echo $hover_color; ?> !important;
    }
    #category-blocks-<?php echo $sec_id; ?>.w3eden .btn{
        background-color: <?php echo $button_color; ?> !important;
        border-color: <?php echo $button_color; ?> !important;
        text-decoration: none !important;
        font-size: 11px;
        letter-spacing: 1.5px;
    }
    #category-blocks-<?php echo $sec_id; ?>.w3eden a:hover .btn{
        background-color: <?php echo $hover_color; ?> !important;
    }
</style>