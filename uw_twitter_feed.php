<?php
/**
* @package UwTwitterFeed
* @version 1.0
*/
/*
Plugin Name: UW-Madison Twitter Feed
Description: A WordPress plugin to connect with Twitter's API via OAuth and return/parse Twitter data.
Author: University Communications and Marketing at the University of Wisconsin-Madison
Version: 1.0
*/

// Load the Twitter Feed lib
require_once(dirname(__FILE__) . '/lib/uw_twitter_feed.class.php');


/**
 * Theme helper function for displaying tweets
 *
 * Renders tweets for Twitter API method call
 * Calls TwiiterFeed::parse()
 *
 * @param $method {string} | Twitter API method path
 * @param $opts {array}
 *
 */
function uw_twitter_feed($method, $opts=array()) {
  $uw_twitter_feed = new UwTwitterFeed();

  echo $uw_twitter_feed->parse($method, $opts);
}