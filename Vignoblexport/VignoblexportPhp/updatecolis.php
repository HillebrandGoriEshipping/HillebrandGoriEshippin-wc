<?php

require_once("../../../../../wp-load.php");


if (WC()->session) {

    WC()->session->set('VINW_CONF_EXP', " ");
} else {
    wp_send_json_error(array('message' => 'could not set point. Woocommerce sessions are not enabled!'));
}

echo WC()->session->get('VINW_CONF_EXP');
