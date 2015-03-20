<?php

if (function_exists('add_action')){
	add_action('init', function()
	{
		// modules: 'sitemap', 'opengraph', 'robots', 'file_editor', 'importer_exporter', 'performance'

		if (function_exists('add_filter')){
			add_filter('aioseop_class_opengraph', function($class)
			{
				return '\atomita\wordpress\allInOneSeoHack\Opengraph';
			});
		}
	}, 0);
}
