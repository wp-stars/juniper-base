<?php
/**
 * Functions for capability changes
 *
 * @package IWGPlating
 * @author LimeSoda
 * @copyright Copyright (c) 2020, LimeSoda
 * @link https://limesoda.com/
 */

namespace Limesoda\Astra_Child\Admin\LS_Capabilities;

/**
 * Allow editors to manage smart slider
 */
add_action('admin_head', function() {
	$editor = get_role('editor');
	if (!$editor->has_cap('smartslider')) {
		$editor->add_cap('smartslider');
	}
	if (!$editor->has_cap('smartslider_config')) {
		$editor->add_cap('smartslider_config');
	}
	if (!$editor->has_cap('smartslider_delete')) {
		$editor->add_cap('smartslider_delete');
	}
	if (!$editor->has_cap('smartslider_edit')) {
		$editor->add_cap('smartslider_edit');
	}
	// careful if we switch to multisite later: https://smartslider.helpscoutdocs.com/article/1983-how-to-give-access-to-smart-slider-for-non-admin-users#wordpress
	if (!$editor->has_cap('unfiltered_html')) {
		$editor->add_cap('unfiltered_html');
	}
});
