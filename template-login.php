<?php

/*
Template Name: Log In
*/

if ( is_user_logged_in() ) {
    header('Location: /wp-admin/');
} else {
    $wpcustomize_login_header_url   = __( 'https://wordpress.org/' );
    $wpcustomize_login_header_title = __( 'Powered by WordPress' );
?><!DOCTYPE html>
<!--[if IE 8]>
<html xmlns="http://www.w3.org/1999/xhtml" class="ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 8) ]><!-->
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
    <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
    <title><?php
        if( get_option('wpcustomize_admin_page_title') ) {
            echo html_entity_decode(get_option('wpcustomize_admin_page_title'));
        } else {
            wp_title( '|', true, 'right' );
        }
    ?></title>
    <?php wp_admin_css( 'login', true ); ?>
    <?php
        echo '<style type="text/css">';
        if( get_option('wpcustomize_admin_logo_image_url') ) {
            echo '#login h1 a {
                background-image:url(' . html_entity_decode(get_option('wpcustomize_admin_logo_image_url')) . ') !important;
                background-size: ' . html_entity_decode(get_option('wpcustomize_admin_logo_width')) . 'px ' . html_entity_decode(get_option('wpcustomize_admin_logo_height')) . 'px !important;
                height: ' . html_entity_decode(get_option('wpcustomize_admin_logo_area_height')) . 'px !important;
                width: ' . html_entity_decode(get_option('wpcustomize_admin_logo_area_width')) . 'px !important;
            }';
        }
        if( get_option('wpcustomize_admin_bgcolor', '000') ) {
            echo 'body { background-color:' . html_entity_decode(get_option('wpcustomize_admin_bgcolor', '000')) . ' !important; }';
        }
        if( get_option('wpcustomize_admin_linkcolor', 'fff') ) {
            echo '#login #nav a, #login #backtoblog a { color:' . html_entity_decode(get_option('wpcustomize_admin_linkcolor', 'fff')) . ' !important; text-shadow: none !important; }';
        }
        if( get_option('wpcustomize_admin_linkhovercolor', 'cfcfcf') ) {
            echo '#login #nav a:hover, #login #backtoblog a:hover { color: ' . html_entity_decode(get_option('wpcustomize_admin_linkhovercolor', 'cfcfcf')) . ' !important; text-shadow: none !important; }';
        }
        if( get_option('wpcustomize_admin_login_background_url') ) {
            echo 'body {
                background-image: url(' . html_entity_decode(get_option('wpcustomize_admin_login_background_url')) . ') !important;
                background-repeat: ' . html_entity_decode(get_option('wpcustomize_admin_login_background_repeat')) . ';
                background-position: ' . html_entity_decode(get_option('wpcustomize_admin_login_background_position')) . ';
                background-attachment: ' . html_entity_decode(get_option('wpcustomize_admin_login_background_attachment')) . ';
                background-size: ' . html_entity_decode(get_option('wpcustomize_admin_login_background_size')) . ';
            }';
        }
        if( get_option('wpcustomize_admin_loginstyles') ) {
            echo html_entity_decode(get_option('wpcustomize_admin_loginstyles'));
        }
        echo '</style>';
    ?>
</head>
<body class="login login-action-login wp-core-ui">
    <div id="login">
        <h1><a href="<?php echo ( get_option('wpcustomize_admin_logo_link_url') ? html_entity_decode(get_option('wpcustomize_admin_logo_link_url')) : site_url('login') . '/' ); ?>" title="<?php echo html_entity_decode(get_option('wpcustomize_admin_logo_title')); ?>" tabindex="-1"><?php bloginfo( 'name' ); ?></a></h1>
        <?php
            // error messages
            if ( isset($_GET['login_error']) ) {
                echo '<div id="login_error">' . ( get_option('wpcustomize_custom_error_message') ? html_entity_decode(get_option('wpcustomize_custom_error_message')) : 'Incorrect login details. Please try again.' ) . '</div>';
            }

            $args = array(
                'echo'           => true,
                'remember'       => true,
                'redirect'       => site_url('login') . '/',
                'form_id'        => 'loginform',
                'id_username'    => 'user_login',
                'id_password'    => 'user_pass',
                'id_remember'    => 'rememberme',
                'id_submit'      => 'wp-submit',
                'label_username' => __( 'Username' ),
                'label_password' => __( 'Password' ),
                'label_remember' => __( 'Remember Me' ),
                'label_log_in'   => __( 'Log In' ),
                'value_username' => '',
                'value_remember' => true
            );
            wp_login_form();
        ?>
    </div>
    <div class="clear"></div>
<?php
    if( get_option('wpcustomize_remember_me_by_default') ) {
        echo "<script>document.getElementById('rememberme').checked = true;</script>";
    }
?>
</body>
</html>
<?php
}
