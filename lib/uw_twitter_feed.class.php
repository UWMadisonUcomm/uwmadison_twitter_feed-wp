<?php

class UwTwitterFeed {

  public $screen_name;
  public $avatar_size;
  public $date_format;

  // Cache expiration in seconds
  public $cache_expiration;

  /**
   * Constructor function
   * Set the default instance variables
   */
  public function __construct() {

    // Twitter credentials
    $this->consumer_key = TWITTER_CONSUMER_KEY;
    $this->consumer_secret = TWITTER_CONSUMER_SECRET;
    $this->consumer_oauth_token = TWITTER_ACCESS_TOKEN;
    $this->access_token_secret = TWITTER_ACCESS_TOKEN_SECRET;

    // Avatar size (width in pixels)
    $this->avatar_size = 48;

    //date format
    $this->date_format = '<span class="uwmadison_tweet_date">%b %e, %l:%M %p</span>';

    // Default the cache to 10 minutes
    $this->cache_expiration = 60 * 10;

    // Load the OAuth library
    require_once(dirname(__FILE__) . '/twitteroauth/twitteroauth.php');

    // establabish the OAuth connect
    $this->oauth =  new TwitterOAuth(
                          $this->consumer_key,
                          $this->consumer_secret,
                          $this->consumer_oauth_token,
                          $this->access_token_secret
                        );
  }

/**
   * the main parse method
   *
   * @param $method {string} | The Twitter API method to call
   * @param $opts {array}
   * @return {string}
   *  Return collection of returned tweets as HTML
   */
  public function parse($method, $opts=array()) {
    if ( $data = $this->getRemoteData($method, $opts) ) {
      $out = '';
      foreach ($data->data as $key => $tweet) {
        $out .= $this->tweetHTML($tweet);
      }
      return $out;
    }
  }

/**
   * format tweet HTML
   *
   * @param $tweet {object} | The tweet
   * @return {string}
   *  Return HTML string for tweet
   */
  public function tweetHTML($tweet) {
    $out = '<div class="post tweet">';
    $out .= '<div class="thumbnail"><a href="http://twitter.com/' . $tweet->user_screen_name . '"><img src="' . $tweet->profile_image_url . '" alt=""></a></div>';
    $out .= '<div class="tweet_text">' . htmlspecialchars_decode($tweet->text) . '<span class="tweet_timestamp"><a href="' . $this->tweetURL($tweet) . '">@' . $tweet->user_screen_name . ' | ' . $tweet->created_at . '</a></span></div>';
    $out .= '</div>';
    return $out;
  }

/**
   * get remote data from Twitter using TwitterOAuth
   *
   * @param $method {string} | The Twitter API method
   * @param $opts {array}
   * @return {array}
   *  Return array with method, timestamp and data
   */
  public function getRemoteData($method,$opts) {
    // Define the cache key
    $cache_key = $this->transientKey($method,$opts);

<<<<<<< HEAD
=======

>>>>>>> e884eb62fd0c8744acbe3e1a2255842fad4d7fc5
    // Pull remote data from the cache or fetch it
    if ( ($remote_cache = get_transient($cache_key)) !== FALSE ) {
      $remote_data = $this->myUnserialize($remote_cache);
    }
    else {
      $twitter_api = $this->oauth;

      $opts = $this->injectDefaultOptions($opts);

      //The twitterOAuth call
      $remote_data = $this->oauth->get($method, $opts['api_options']);

      //cache the results if we get any back from Twitter
      if (count($remote_data) > 0) {
        $response_serialized = $this->mySerialize($remote_data);
        set_transient($cache_key, $response_serialized, $this->cache_expiration);
      }
      else {
        $remote_data = FALSE; //No tweets returned
      }
    }

    if ( $remote_data !== FALSE ) {
      $data = $this->processRemoteData($remote_data, $opts);

      $out = (object) array(
        'method' => $opts['method'],
        'timestamp' => time(),
        'data' => $data,
      );
      return $out;
    }
    else {
      return FALSE;
    }
  }

/**
   * parse remote Twitter data into data object for our needs
   *
   * @param $data {array}
   * @param $opts {array}
   * @return {array}
   *  Return parsed array
   */
  function processRemoteData($data,$opts) {
    $out = array();
    foreach ($data as $tweet) {
      $t = (object) array(
        'text' => $this->parseTweet(htmlspecialchars($tweet->text)),
        'user_screen_name' => htmlspecialchars($tweet->user->screen_name),
        'created_at' => strftime($this->date_format,strtotime($tweet->created_at)),
        'profile_image_url' => $tweet->user->profile_image_url,
        'id' => $tweet->id,
      );
      $out[] = $t;
    }

    return $out;
  }

/**
   * generate permalink URL
   *
   * @param $tweet {object} | tweet object
   * @return {str}
   *  Return URL
   */
 public function tweetURL($tweet)
  {
    return $url = "http://twitter.com/" . $tweet->user_screen_name . "/statuses/" . $tweet->id;
  }


  /**
   * replace twitter handles, hashtags and URLs in tweet->text with links
   *
   * @param $str {str} | the text node returned from Twitter API
   * @return {str}
   *  Return parsed text
   *  From: http://www.nilambar.net/2012/07/how-to-parse-hashtag-mention-and-url-in.html
   */
 public function parseTweet($str)
  {
    $patterns = array();
    $replace = array();

    //parse URL
    preg_match_all("/[A-Za-z]+:\/\/[A-Za-z0-9-_]+\.[A-Za-z0-9-_:%&~\?\/.=]+[A-Za-z0-9-_:%&~\?\/=]/",$str,$urls);
    foreach($urls[0] as $url)
    {
      $patterns[] = $url;
      $replace[] = '<a href="'.$url.'" >'.$url.'</a>';
    }

    //parse hashtag
    preg_match_all("/[#]+([a-zA-Z0-9_]+)/",$str,$hashtags);
    foreach($hashtags[1] as $hashtag)
    {
      $patterns[] = '#'.$hashtag;
      $replace[] = '<a href="http://search.twitter.com/search?q='.$hashtag.'" >#'.$hashtag.'</a>';
    }

    //parse mention
    preg_match_all("/ [@]+([a-zA-Z0-9_]+)/",$str,$usernames);
    foreach($usernames[1] as $username)
    {
      $patterns[] = '@'.$username;
      $replace[] = ' <a href="http://twitter.com/'.$username.'" >@'.$username.'</a>';
    }
    //replace now
    $str = str_replace($patterns,$replace,$str);

    return $str;
  }

  /**
   * generate key for WP transient cache
   *
   * @param $opts {array}
   * @return {array}
   *  Return default injected options array
   */
  private function transientKey($method,$opts) {
    // Needs to be less than 40 characters
    // md5() hex hashes are 32 characters
    return "tw_" . md5($method . $opts->screen_name);
  }

  /**
   * Inject defaults
   *
   * @param $opts {array}
   * @return {array}
   *  Return default injected options array
   */
  private function injectDefaultOptions($opts) {
    // Defaults - api_options are for Twitter's API
    $defaults = array(
      'title' => 'Follow @' . $this->screen_name . ' on Twitter',
      'avatar_size' => 48,
      'header_tag' => 'h2',
      'api_options' => array('include_entities' => 'false', 'count' => 10)
    );

    // Merge in the defaults
    $opts = array_merge($defaults, $opts);

    return $opts;
  }

  /**
   * serialize and compress response
   *
   * @param $response {array}
   * @return serialized object
   *  Return serialized and compressed response object
   */
  private function mySerialize($response) {
    $response_serialized = serialize($response);
    if (extension_loaded('zlib')) {
      $response_serialized = base64_encode(gzcompress($response_serialized));
    }
    return $response_serialized;
  }

  /**
   * Unserialize and decompress response
   *
   * @param $response {array}
   * @return object
   *  Return unserialized and uncompressed object
   */
  private function myUnserialize($response) {
    if (extension_loaded('zlib')) {
      $response = gzuncompress(base64_decode($response));
    }
    $response_unserialized = unserialize($response);
    return $response_unserialized;
  }

}