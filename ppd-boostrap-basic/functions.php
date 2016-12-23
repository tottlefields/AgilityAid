<?php
session_start();

// Register Custom Navigation Walker
require_once('wp_bootstrap_navwalker.php');

register_nav_menu(
	'main-menu',
	'Main Menu'
);


?>