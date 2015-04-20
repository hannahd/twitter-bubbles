<?php

function print_to_console($msg){
    return '<script type="text/javascript" charset="utf-8">console.log("'.$msg.'");</script>';
}


/**
 * Twitter Entities Linker class.
 *
 * PHP versions 5
 *
 * Copyright 2010, ogaoga.org
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2010, ogaoga.org
 * @link          http://www.ogaoga.org/
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Twitter Entities Linker class
 *
 * usage:  TwitterEntitiesLinker::getHtml($tweet);
 *
 */
class TwitterEntitiesLinker {

  /**
   * get html source
   *
   * @param  $tweet : a json decoded tweet object
   * @param  $highlight : set params if you want to highlight any text
   *                      (optional)
   *
   * $highlight should be set with preg_replace params like the following:
   *
   *    $highlight = array('patterns'
   *                         => array('/notice/i',
   *                                  '/caution/i',
   *                                  ...),
   *                       'replacements'
   *                         => array('<span class="notice">$0</span>',
   *                                  '<b>$0</b>',
   *                                  ...)
   *                       );
   *
   * @return html source
   */
  public static function getHtml($tweet, $highlight = array()) {
    $convertedEntities = array();

    // check entities data exists
    if ( ! isset($tweet->entities) ) {
      return $tweet->text;
    }

    // make entities array
    foreach ( $tweet->entities as $type => $entities ) {
      foreach ( $entities as $entity ) {
        $entity->type = $type;
        $convertedEntities[] = $entity;
      }
    }

    // sort entities
    usort(&$convertedEntities,
          "TwitterEntitiesLinker::sortFunction");

    // split entities and texts
    $pos = 0;
    $entities = array();
    foreach ($convertedEntities as $entity) {
      // not entity
      if ( $pos < $entity->indices[0] ) {
        $substring = mb_substr($tweet->text,
                               $pos,
                               $entity->indices[0] - $pos,
                               'utf-8');
        $entities[] = array('text' => $substring, 
                            'data' => null);
        $pos = $entity->indices[0];
      }
      // entity
      $substring = mb_substr($tweet->text,
                             $pos,
                             $entity->indices[1] - $entity->indices[0],
                             'utf-8');
      $entities[] = array('text' => $substring, 
                          'data' => $entity);
      $pos = $entity->indices[1];
    }
    // tail of not entity
    $length = mb_strlen($tweet->text, 'utf-8');
    if ( $pos < $length ) {
      $substring = mb_substr($tweet->text,
                             $pos,
                             $length - $pos,
                             'utf-8');
      $entities[] = array('text' => $substring, 
                          'data' => null);
    }

    // replace
    $html = "";
    foreach ( $entities as $entity ) {
      if ( $entity['data'] ) {
        if ( $entity['data']->type == 'urls' ) {
		  $display_url = ($entity['data']->display_url) ? $entity['data']->display_url : $entity['data']->url;
          $url = ($entity['data']->expanded_url) ? $entity['data']->expanded_url : $entity['data']->url;
          $html .= '<a href="'.$url.'" target="_blank" rel="nofollow" class="twitter-timeline-link">'.self::highlightText($display_url, $highlight).'</a>';
        }
        else if ( $entity['data']->type == 'hashtags' ) {
          $text = $entity['data']->text;
          $html .= '<a href="http://twitter.com/#!/search?q=%23'.$text.'" title="#'.$text.'" target="_blank" class="twitter-hashtag" rel="nofollow">#'.self::highlightText($text, $highlight).'</a>';
        }
        else if ( $entity['data']->type == 'user_mentions' ) {
          $screen_name = $entity['data']->screen_name;
          $html .= '@<a class="twitter-atreply" data-screen-name="'.$screen_name.'" target="_blank" href="http://twitter.com/'.$screen_name.'" rel="nofollow">'.self::highlightText($screen_name, $highlight).'</a>';
        }
        else {
        }
      }
      else {
        $html .= self::highlightText($entity['text'], $highlight);
      }
    }
    // return 
    return $html;
  }

  /**
   * sort function
   *
   * @param   data a
   * @param   data b
   * @return  1 or -1 or 0
   */
  static private function sortFunction($a, $b)  {
    if ($a->indices > $b->indices) { return 1; }
    else if ($a->indices < $b->indices) { return -1; }
    else { return 0; }
  }

  /**
   * highlight text
   */
  static private function highlightText($text, $highlight) {
    if ( $highlight ) {
      $text = preg_replace($highlight['patterns'],
                           $highlight['replacements'],
                           $text);
    }
    return $text;
  }
}

function ranger($url){
    $headers = array(
    "Range: bytes=0-32768"
    );

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($curl);
    curl_close($curl);
    return $data;
}



function get_formatted_date_dif($timestamp, $reference = null){
		$MINUTE = 60;
		$HOUR = 3600;
		$DAY = 86400;
		$WEEK = 604800;
		$MONTH = 2628000;
		$YEAR = 31536000;
		
		$formats = array(
		    array(0.7 * $MINUTE, 'now',       1),
		    array(1.5 * $MINUTE, '1 min',   1),
		    array( 60 * $MINUTE, '%d mins', $MINUTE),
		    array(1.5 * $HOUR,   '1 hr',    1),
		    array(      $DAY,    '%d hrs',   $HOUR),
		    array(  2 * $DAY,    '1 day',      1),
		    array(  7 * $DAY,    '%d days',    $DAY));
		
		if($timestamp )
		
		if ($reference === null) {
            $reference = time();
        }

        $delta = $reference - $timestamp;

        foreach ($formats as $format) {
            if ($delta < $format[0]) {
                return sprintf($format[1], round($delta / $format[2]));
            }
        };
		if($delta < $YEAR){
			return date("M j", $timestamp);
		}else{
			return date("M j, Y", $timestamp);
		}
}

?>