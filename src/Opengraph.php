<?php

namespace atomita\wordpress\allInOneSeoHack;

/**
 * hack the All_in_One_SEO_Pack_Opengraph
 */
class Opengraph extends \All_in_One_SEO_Pack_Opengraph
{
	static public $searchEnabled = false;

	public function __construct(){
		parent::__construct();

		add_filter('aiosp_opengraph_meta', array($this, 'valueFilter'), 10, 3);
	}

	public function valueFilter($filtered_value, $t, $k){
		return apply_filters(
			"aiosp_opengraph_meta_{$t}_{$k}",
			apply_filters(
				"aiosp_opengraph_meta_{$k}",
				$filtered_value, $t));
	}

	public function add_meta( ) {
		global $post, $aiosp, $aioseop_options, $wp_query;
		$metabox = $this->get_current_options( Array(), 'settings' );
		$key = $this->options['aiosp_opengraph_key'];
		$dimg = $this->options['aiosp_opengraph_dimg'];
		$current_post_type = get_post_type();
		$title = $description = $image = $video = '';
		$type = $this->type;
		$sitename = $this->options['aiosp_opengraph_sitename'];
		
		if ( !empty( $aioseop_options['aiosp_hide_paginated_descriptions'] ) ) {
			$first_page = false;
			if ( $aiosp->get_page_number() < 2 ) $first_page = true;				
		} else {
			$first_page = true;
		}
		$url = $aiosp->aiosp_mrt_get_url( $wp_query );
		$url = apply_filters( 'aioseop_canonical_url', $url );;
		
		$setmeta = $this->options['aiosp_opengraph_setmeta'];
		if ( is_front_page() ) {
			$title = $this->options['aiosp_opengraph_hometitle'];
			if ( $first_page )
				$description = $this->options['aiosp_opengraph_description'];
			if ( !empty( $this->options['aiosp_opengraph_homeimage'] ) )
				$thumbnail = $this->options['aiosp_opengraph_homeimage'];
			else
				$thumbnail = $this->options['aiosp_opengraph_dimg'];
			
			/* If Use AIOSEO Title and Desc Selected */
			if( $setmeta ) {
				$title = $aioseop_options['aiosp_home_title'];
				if ( $first_page )
					$description = $aioseop_options['aiosp_home_description'];
			}
			
			/* Add some defaults */
			if( empty($title) ) $title = get_bloginfo('name');
			if( empty($sitename) ) $sitename = get_bloginfo('name');
			
			if ( empty( $description ) && $first_page && ( !empty( $this->options['aiosp_opengraph_generate_descriptions'] ) ) && !empty( $post ) && !empty( $post->post_content ) )
				$description = $aiosp->trim_excerpt_without_filters( $aiosp->internationalize( preg_replace( '/\s+/', ' ', $post->post_content ) ), 1000 );
			
			if ( empty($description) && $first_page ) $description = get_bloginfo('description');
			if ( $type == 'article' && ( !empty( $this->options['aiosp_opengraph_hometag'] ) ) ) {
				$tag = $this->options['aiosp_opengraph_hometag'];
			}
		} elseif ( is_singular( ) && $this->option_isset('types') 
		&& is_array( $this->options['aiosp_opengraph_types'] ) 
		&& in_array( $current_post_type, $this->options['aiosp_opengraph_types'] ) ) {
			
			if ( $type == 'article' ) {
				if ( !empty( $metabox['aioseop_opengraph_settings_section'] ) ) {
					$section = $metabox['aioseop_opengraph_settings_section'];
				}
				if ( !empty( $metabox['aioseop_opengraph_settings_tag'] ) ) {
					$tag = $metabox['aioseop_opengraph_settings_tag'];
				}
				if ( !empty( $this->options['aiosp_opengraph_facebook_publisher'] ) ) {
					$publisher = $this->options['aiosp_opengraph_facebook_publisher'];
				}
			}
			
			if ( !empty( $this->options['aiosp_opengraph_twitter_domain'] ) )
				$domain = $this->options['aiosp_opengraph_twitter_domain'];
			
			
			if ( $type == 'article' && !empty( $post ) ) {
				if ( isset( $post->post_author ) && !empty( $this->options['aiosp_opengraph_facebook_author'] ) )
					$author = get_the_author_meta( 'facebook', $post->post_author );
				
				if ( isset( $post->post_date ) )
					$published_time = date( 'Y-m-d\TH:i:s\Z', mysql2date( 'U', $post->post_date ) );	
				
				if ( isset( $post->post_modified ) )
					$modified_time = date( 'Y-m-d\TH:i:s\Z', mysql2date( 'U', $post->post_modified ) );
			}
			
			$image = $metabox['aioseop_opengraph_settings_image'];
			$video = $metabox['aioseop_opengraph_settings_video'];
			$title = $metabox['aioseop_opengraph_settings_title'];
			$description = $metabox['aioseop_opengraph_settings_desc'];
			
			/* Add AIOSEO variables if Site Title and Desc from AIOSEOP not selected */
			global $aiosp;
			if( empty( $title ) )
				$title = $aiosp->get_aioseop_title( $post );
			if ( empty( $description ) )
				$description = trim( strip_tags( get_post_meta( $post->ID, "_aioseop_description", true ) ) );
			
			/* Add some defaults */
			if ( empty( $title ) ) $title = get_the_title();
			if ( empty( $description ) && ( $this->options['aiosp_opengraph_generate_descriptions'] ) )
				$description = $post->post_content;
			if ( empty( $type ) ) $type = 'article';
		}
		else if ((is_array( $this->options['aiosp_opengraph_types'] ) 
		&& in_array( $current_post_type, $this->options['aiosp_opengraph_types'] ) )
		|| (self::$searchEnabled && is_search())){
			// hack!
			if ( empty( $type ) ) $type = 'website';
		}
		else return;
		
		if ( $type == 'article' || $type == 'website') { // hack!
			if ( !empty( $this->options['aiosp_opengraph_gen_tags'] ) ) {
				if ( !empty( $this->options['aiosp_opengraph_gen_keywords'] ) ) {
					$keywords = $aiosp->get_main_keywords();
					$keywords = $this->apply_cf_fields( $keywords );
					$keywords = apply_filters( 'aioseop_keywords', $keywords );
					if ( !empty( $keywords ) && !empty( $tag ) ) {
						$tag .= ',' . $keywords;
					} elseif ( empty( $tag ) ) {
						$tag = $keywords;
					}
				}
				$tag = $aiosp->keyword_string_to_list( $tag );
				if ( !empty( $this->options['aiosp_opengraph_gen_categories'] ) )
					$tag = array_merge( $tag, $aiosp->get_all_categories( $post->ID ) );
				if ( !empty( $this->options['aiosp_opengraph_gen_post_tags'] ) )
					$tag = array_merge( $tag, $aiosp->get_all_tags( $post->ID ) );
			}
			if ( !empty( $tag ) )
				$tag = $aiosp->clean_keyword_list( $tag );			
		}
		if ( !empty( $description ) )
			$description = $aiosp->trim_excerpt_without_filters( $aiosp->internationalize( preg_replace( '/\s+/', ' ', $description ) ), 1000 );
		
		$title = $this->apply_cf_fields( $title );
		$description = $this->apply_cf_fields( $description );
		
		/* Data Validation */			
		$title = strip_tags( esc_attr( $title ) );
		$sitename = strip_tags( esc_attr( $sitename ) );
		$description = strip_tags( esc_attr( $description ) );
		
		if ( empty( $thumbnail ) && !empty( $image ) )
			$thumbnail = $image;
		
		/* Get the first image attachment on the post */
		// if( empty($thumbnail) ) $thumbnail = $this->get_the_image();
		
		/* Add user supplied default image */
		if( empty($thumbnail) ) {
			if ( empty( $this->options['aiosp_opengraph_defimg'] ) )
				$thumbnail = $this->options['aiosp_opengraph_dimg'];
			else {
				switch ( $this->options['aiosp_opengraph_defimg'] ) {
					case 'featured'	:	$thumbnail = $this->get_the_image_by_post_thumbnail( );
						break;
					case 'attach'	:	$thumbnail = $this->get_the_image_by_attachment( );
						break;
					case 'content'	:	$thumbnail = $this->get_the_image_by_scan( );
						break;
					case 'custom'	:	$meta_key = $this->options['aiosp_opengraph_meta_key'];
						if ( !empty( $meta_key ) && !empty( $post ) ) {
							$meta_key = explode( ',', $meta_key );
							$thumbnail = $this->get_the_image_by_meta_key( Array( 'post_id' => $post->ID, 'meta_key' => $meta_key ) );				
						}
						break;
					case 'auto'		:	$thumbnail = $this->get_the_image();
						break;
					case 'author'	:	$thumbnail = $this->get_the_image_by_author();
						break;
					default			:	$thumbnail = $this->options['aiosp_opengraph_dimg'];
				}
			}
		}
		
		if ( ( empty( $thumbnail ) && !empty( $this->options['aiosp_opengraph_fallback'] ) ) )
			$thumbnail = $this->options['aiosp_opengraph_dimg'];
		
		if ( !empty( $thumbnail ) ) $thumbnail = esc_url( $thumbnail );
		
		$width = $height = '';
		if ( !empty( $thumbnail ) ) {
			if ( !empty( $metabox['aioseop_opengraph_settings_imagewidth'] ) )
				$width = $metabox['aioseop_opengraph_settings_imagewidth'];
			if ( !empty( $metabox['aioseop_opengraph_settings_imageheight'] ) )
				$height = $metabox['aioseop_opengraph_settings_imageheight'];
		}
		
		if ( !empty( $video ) ) {
			if ( !empty( $metabox['aioseop_opengraph_settings_videowidth'] ) )
				$videowidth = $metabox['aioseop_opengraph_settings_videowidth'];
			if ( !empty( $metabox['aioseop_opengraph_settings_videoheight'] ) )
				$videoheight = $metabox['aioseop_opengraph_settings_videoheight'];				
		}
		
		$card = 'summary';
		if ( !empty( $this->options['aiosp_opengraph_defcard'] ) )
			$card = $this->options['aiosp_opengraph_defcard'];
		
		if ( !empty( $metabox['aioseop_opengraph_settings_setcard'] ) )
			$card = $metabox['aioseop_opengraph_settings_setcard'];
		
		$site = $domain = $creator = '';
		
		if ( !empty( $this->options['aiosp_opengraph_twitter_site'] ) )
			$site = $this->options['aiosp_opengraph_twitter_site'];
		
		if ( !empty( $this->options['aiosp_opengraph_twitter_domain'] ) )
			$domain = $this->options['aiosp_opengraph_twitter_domain'];
		
		if ( !empty( $post ) && isset( $post->post_author ) && !empty( $this->options['aiosp_opengraph_twitter_creator'] ) )
			$creator = get_the_author_meta( 'twitter', $post->post_author );
		
		if ( !empty( $site ) && $site[0] != '@' ) $site = '@' . $site;

		if ( !empty( $creator ) && $creator[0] != '@' ) $creator = '@' . $creator;
		
		$meta = Array(
			'facebook'	=> Array(
				'title'			=> 'og:title',
				'type'			=> 'og:type',
				'url'			=> 'og:url',
				'thumbnail'		=> 'og:image',
				'width'			=> 'og:image:width',
				'height'		=> 'og:image:height',
				'video'			=> 'og:video',
				'videowidth'	=> 'og:video:width',
				'videoheight'	=> 'og:video:height',
				'sitename'		=> 'og:site_name',
				'key'			=> 'fb:admins',
				'description'	=> 'og:description',
				'section'		=> 'article:section',
				'tag'			=> 'article:tag',
				'publisher'		=> 'article:publisher',
				'author'		=> 'article:author',
				'published_time'=> 'article:published_time',
				'modified_time'	=> 'article:modified_time',
			),
			'twitter'	=> Array(
				'card'			=> 'twitter:card',
				'site'			=> 'twitter:site',
				'creator'		=> 'twitter:creator',
				'domain'		=> 'twitter:domain',
				'description'	=> 'twitter:description',
			),
			'google+'	=> Array(
				'thumbnail'		=> 'image',
			),
		);
		
		// Add links to testing tools
		
		/*
		http://developers.facebook.com/tools/debug
		https://dev.twitter.com/docs/cards/preview
		http://www.google.com/webmasters/tools/richsnippets
		*/
		/*
		$meta = Array(
			'facebook'	=> Array(
					'title'			=> 'og:title',
					'type'			=> 'og:type',
					'url'			=> 'og:url',
					'thumbnail'		=> 'og:image',
					'sitename'		=> 'og:site_name',
					'key'			=> 'fb:admins',
					'description'	=> 'og:description'
				),
			'google+'	=> Array(
					'thumbnail'		=> 'image',
					'title'			=> 'name',
					'description'	=> 'description'
				),
			'twitter'	=> Array(
					'card'			=> 'twitter:card',
					'url'			=> 'twitter:url',
					'title'			=> 'twitter:title',
					'description'	=> 'twitter:description',
					'thumbnail'		=> 'twitter:image'
						
					)
			);
		*/
		
		$tags = Array(
				'facebook'	=> Array( 'name' => 'property', 'value' => 'content' ),
				'twitter'	=> Array( 'name' => 'name', 'value' => 'content' ),
				'google+'	=> Array( 'name' => 'itemprop', 'value' => 'content' )
		);
		
		foreach ( $meta as $t => $data )
			foreach ( $data as $k => $v ) {
				if ( empty( $$k ) ) $$k = '';
				$filtered_value = $$k;
				$filtered_value = apply_filters( $this->prefix . 'meta', $filtered_value, $t, $k );
				if ( !empty( $filtered_value ) ) {
					if ( !is_array( $filtered_value ) )
						$filtered_value = Array( $filtered_value );
					foreach( $filtered_value as $f ) {
						echo '<meta ' . $tags[$t]['name'] . '="' . $v . '" ' . $tags[$t]['value'] . '="' . $f . '" />' . "\n";							
					}
				}
			}
	}

	public function type_setup(){
		if ( !empty( $this->options ) && !empty( $this->options['aiosp_opengraph_categories'] ) ){
			$this->type = $this->options['aiosp_opengraph_categories'];
		}
		parent::type_setup();
	}

}
