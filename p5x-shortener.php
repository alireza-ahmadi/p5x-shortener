<?php
	/*
	Plugin Name: P5X.co Shortener
	Plugin URI: http://alireza.es/projects/p5x-shortener-wp
	Description: افزونه ای برای ایجاد لینک کوتاه پست های شما توسط سرویس کوتاه کننده لینک ققنوس
	Version: 1.0
	Author: Alireza Ahmadi
	Author URI: http://alireza.es
	License: GPL2
	*/
	class Phoenix{
		private $APIURL;
		
		public function __construct(){
			$this->APIURL = 'http://p5x.co/api/shorten/';
			add_shortcode('p5x', array($this, 'shortcode'));
			add_action('edit_post', array($this, 'flush'));
		}
		// ----------- Processes
		private function create($url, $type, $custom=null){
			$url = urlencode($url);
			if($type=='alpha' || $type=='id'){
				$args = array('type' => $type , 'url' => $url);
			}
			else if($type=='custom'){
				if(strlen($custom) >= 3 && strlen($custom) <= 10){
					$args = array('type' => 'custom' , 'url' => $url , 'custom_data' => $custom);
				}
				else{
					return 'ERROR';
				}
			}
			else{
				return 'ERROR';
			}
			foreach($args as $each=>$value){
				$arguments[] = $each.'='.$value;
			}
			$json_response = wp_remote_post($this->APIURL .'?'. (implode('&',$arguments)));
			if(!is_wp_error($json_response)){
				if (intval($json_response['response']['code']) == 200) {	
					$response = json_decode($json_response['body']);
					if($response->status == 'success'){
						$return = $response->message;
					}
					else{
						$return = 'ERROR';
					}
				}
				else{
					$return = 'ERROR';
				}
			}
			else{
				return 'ERROR';
			}
			
			return $return;
		}
		
		public function shortcode($attr){
			global $post;
			if(get_post_meta($post->ID, 'short_url', true) != ""){
				$short_url = get_post_meta($post->ID, 'short_url', true);
			}else{
				extract(shortcode_atts(array(
					'type' => 'alpha', // id , alpha , custom
					'custom' => null,
					'link' => 'false'
				), $attr));
				
				$full_url = get_permalink();
				$short_url = $this->create($full_url, $type, $custom);
				
				if($short_url != 'ERROR'){
					add_post_meta($post->ID, 'short_url', $short_url, true);
				}
				else{
					$short_url = $full_url;
				}
			}
			if($link == 'true'){
				$short_url = '<a href="'.$short_url.'">لینک کوتاه</a>';
			}
			return $short_url;
		}
		
		public function flush(){
			global $post;
			delete_post_meta($post->ID, 'short_url');
		}
		
	}
	
	$phoenix = new Phoenix();
	
?>