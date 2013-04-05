<?php
/**
 * This is a sample shortcode script for the Libre theme. The theme includes all 
 * PHP files in the shortcodes folder using their filename as identifier for the 
 * shortcode. Output as well as the return value from the script are used for 
 * the shortcode's output. Using return will output the supplied value and 
 * terminate the shortcode script.
 * 
 * The parameters for the shortcode are available as vars $atts, $content and 
 * $code.
 * 
 * Notice: Returning int 1 won't be used as output because it equals the success 
 * code for the include.
 * 
 * @author Paul van der Meijs <code@lowtone.nl>
 * @copyright Copyright (c) 2011-2012, Paul van der Meijs
 * @license http://wordpress.lowtone.nl/license/
 * @version 1.0
 */

if (is_single())
	return false;

return function($atts, $content, $code, $file) {
	$color = trim(@$atts["color"]) ?: "silver";
	$content = trim($content) ?: __("Read more", "lowtone_libre");
	$link = get_permalink();
		
	if (($linkMetaKey = trim(@$atts["link_meta"])) 
		&& ($linkMetaValue = trim(get_post_meta($GLOBALS["post"]->ID, $linkMetaKey, true))))
			$link = $linkMetaValue;
			
	else if ($attLink = trim(@$atts["link"]))
		$link = $attLink;
		
	$target = trim(@$atts["target"]) ?: "_self";

	return '<div class="button_container">'
		. '<a href="' . $link . '" target="' . $target . '" class="button readmore ' . $color . '">' . $content . '</a>'
		. '</div>';
};