<?php
/**
* Plugin file for dreebbble.
* 
* This file must be placed in the
* /expressionengine/third_party/dreebbble folder in your ExpressionEngine installation.
*
* @package Dreebbble
* @version 1.4
* @author Olivier Bon <oli@builtwithmomentum.com>
* @link http://olivierbon.com/projects/dreebbble
* @copyright Copyright (c) 2010, Olivier Bon Studios
* @license http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons Attribution-Share Alike 3.0 Unported
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array('pi_name'        => 'Dreebbble',
                     'pi_version'     => '1.4',
                     'pi_author'      => 'Olivier Bon',
                     'pi_author_url'  => 'http://olivierbon.com',
                     'pi_description'	=> 'Retrieve latest shots from a dribbble player or a list of shots',
                     'pi_usage'       => Dreebbble::usage()
                     );
/**
 * Dreebbble Class
 *
 * @package Dreebbble
 * @category Plugin
 * @author Olivier Bon
 * @copyright Copyright (c) 2010, Olivier Bon Studios
 * @link http://olivierbon.com/projects/dreebbble
 */

define("ENDPOINT", "http://api.dribbble.com/");

class Dreebbble {
  
  var $return_data;
  protected $player;
  protected $limit;
  protected $list;
  protected $image_size;
  private $EE;
  
  /**
	* Fetch latest shot for a given player
	**/

	public function get_latest()
	{
	  $this->EE =& get_instance();
		$list = ( $this->EE->TMPL->fetch_param('list') );
		$image_size = ( $this->EE->TMPL->fetch_param('image_size') ) ? $this->EE->TMPL->fetch_param('image_size'): 'thumb';
		$limit = ( $this->EE->TMPL->fetch_param('limit') ) ? $this->EE->TMPL->fetch_param('limit'): '5';
		if($list)
		{
			$params = "shots/$list?per_page=$limit";
		}
		else
		{
			$player = $this->EE->TMPL->fetch_param('player');
			$params = "players/$player/shots?per_page=$limit";
		}

		if ( ! $items = $this->dribbble_json2php($params)) 
		{
			return false;
		}
		
		if (count($items) == 0)
		{
			return $this->EE->TMPL->no_results;
		}

		// ----------------------------------------
		//   Loop through the shots
		// ----------------------------------------
		foreach($items->shots as $shot)
		{
			$tagdata	= $this->EE->TMPL->tagdata;
			//	Set shot attributes
			$id = $shot->id;
			$title = $shot->title;
			$url = $shot->url;
			$image_url = $shot->image_url;
			$image_teaser_url = $shot->image_teaser_url;
			$width = $shot->width;
			$height = $shot->height;
			$views_count = $shot->views_count;
			$likes_count = $shot->likes_count;
			$comments_count = $shot->comments_count;
			$rebounds_count = $shot->rebounds_count;
			//	Set player attributes
			$player_id = $shot->player->id;
			$player_name = $shot->player->name;
			$player_url = $shot->player->url;
			$player_avatar_url = $shot->player->avatar_url;
			$player_location = $shot->player->location;
    
			// ----------------------------------------
			//   Parse "single" variables
			// ----------------------------------------	
			foreach ($this->EE->TMPL->var_single as $var)
			{
				//  Parse shot id
				if ($var == 'id')
				{					
					$tagdata	= $this->EE->TMPL->swap_var_single($var, $id, $tagdata);
				}
				//  Parse shot title
				if ($var == 'title')
				{					
					$tagdata	= $this->EE->TMPL->swap_var_single($var, $title, $tagdata);
				}
				//  Parse shot url
				if ($var == 'url')
				{					
					$tagdata	= $this->EE->TMPL->swap_var_single($var, $url, $tagdata);
				}
				//  Parse image url
				if ($var == 'image_url')
				{
					if($image_size == "original")
					{
						$tagdata	= $this->EE->TMPL->swap_var_single($var, $image_url, $tagdata);
					}
					elseif($image_size == "thumb")
					{
						$tagdata	= $this->EE->TMPL->swap_var_single($var, $image_teaser_url, $tagdata);
					}
				}
				//  Parse image width
				if ($var == 'width')
				{					
					$tagdata	= $this->EE->TMPL->swap_var_single($var, $width, $tagdata);
				}
				//  Parse image height
				if ($var == 'height')
				{					
					$tagdata	= $this->EE->TMPL->swap_var_single($var, $height, $tagdata);
				}
				//  Parse image views_count
				if ($var == 'views_count')
				{					
					$tagdata	= $this->EE->TMPL->swap_var_single($var, $views_count, $tagdata);
				}
				//  Parse image likes_count
				if ($var == 'likes_count')
				{					
					$tagdata	= $this->EE->TMPL->swap_var_single($var, $likes_count, $tagdata);
				}
				//  Parse image comments_count
				if ($var == 'comments_count')
				{					
					$tagdata	= $this->EE->TMPL->swap_var_single($var, $comments_count, $tagdata);
				}
				//  Parse image rebounds_count
				if ($var == 'rebounds_count')
				{					
					$tagdata	= $this->EE->TMPL->swap_var_single($var, $rebounds_count, $tagdata);
				}
				//  Parse player_id
				if ($var == 'player_id')
				{					
					$tagdata	= $this->EE->TMPL->swap_var_single($var, $player_id, $tagdata);
				}
				//  Parse player_name
				if ($var == 'player_name')
				{					
					$tagdata	= $this->EE->TMPL->swap_var_single($var, $player_name, $tagdata);
				}
				//  Parse player_avatar_url
				if ($var == 'player_avatar_url')
				{					
					$tagdata	= $this->EE->TMPL->swap_var_single($var, $player_avatar_url, $tagdata);
				}
				//  Parse player_location
				if ($var == 'player_location')
				{					
					$tagdata	= $this->EE->TMPL->swap_var_single($var, $player_location, $tagdata);
				}
				//  Parse image url
			}				
			//	End parse single
		
			$this->return_data .= $tagdata;
		}
		////	End loop through the results
		
		return $this->return_data;
	}

	//	End get_latest

	function dribbble_json2php($params)
	{
		$file = ENDPOINT.$params;
		
		$ch = curl_init($file);
 		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 		$contents = curl_exec($ch);
		
		
		if ($contents)
		{
			$items = json_decode($contents);
			return $items;
		}
		else
		{
			echo "Dreebbble requires the CURL library to be installed. Please check with your hosting provider";
		}
	}//	End dribbble_json2php()

	// --------------------------------------------------------------------
	
	/**
	 * Usage
	 *
	 * Plugin Usage
	 *
	 * @access	public
	 * @return	string
	 */
	
	function usage()
	{
		ob_start(); 
?>
This plugin allows you to display the latest shots for a given player or from a chosen list.

The tags works this way:

:: Single Player:

{exp:dreebbble:get_latest player="dribbble" limit="5"}
{/exp:dreebbble:get_latest}

:: List:

{exp:dreebbble:get_latest list="everyone" limit="5"}
{/exp:dreebbble:get_latest}

Available Parameters so far:

player: This may be a player id or username, e.g. '39' or 'dribbble'.
limit: Use this to limit the amount of shots you would like to show.
list: This enables you to retrieve one of the 3 available lists on dribbble. Use debuts, everyone or popular
image_size: By default the plugin will return the small version of the shot. However, you can set image_size to "original" to return the main image.

Variables available:

id: The shot's id
title: The shot's title
url: The shot's url
image_url: The url for the shot's image
width: The shot's width
height: The shot's height
view_count: The shot's view count
likes_count: The shot's like count
comments_count: The shot's comment count
rebounds_count: The shot's rebound count

player_id: The player's id
player_name: The player's name
player_location: The player's location
player_avatar_url: The player's avatar url


Here's an example of how to use it in your templates:

The below will retrieve a given player's 5 latest shots (large image)

<ul>
{exp:dreebbble:get_latest player="dribbble" limit="5" image_size="original"}
	<li>
		<h2><a href="{url}">{title}</a></h2>
		<h3>By: {player_name} from {player_location}</h3>
		<img src="{image_url}" alt="{title}">
	</li>
{/exp:dreebbble:get_latest}
</ul>

This example will retrieve the five latest shots from everyone (thumbnail version)

<ul>
{exp:dreebbble:get_latest list="everyone" limit="5"}
	<li>
		<h2><a href="{url}">{title}</a></h2>
		<h3>By: {player_name} from {player_location}</h3>
		<img src="{image_url}" alt="{title}">
	</li>
{/exp:dreebbble:get_latest}
</ul>
		
		
<?php
		$buffer = ob_get_contents();
			
		ob_end_clean(); 
		
		return $buffer;
	}
	// END
}
// END CLASS
/* End of file pi.dreebbble.php */
/* Location: ./system/expressionengine/third_party/dreebbble/pi.dreebbble.php */