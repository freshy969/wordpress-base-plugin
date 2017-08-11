<?php
namespace VendorName\MyPlugin;

class Cache extends Plugin {

  /**
    * Retrieves value from cache, if enabled/present, else returns value
    *    generated by callback().
    *
    * @param string $key Key value of cache to retrieve
    * @param function $callback Result to return/set if does not exist in cache
    * @return string Cached value of key
    */
  public static function get_object( $key = null, $callback ) {

    $object_cache_group = isset( self::$settings['object_cache_group']) && self::$settings['object_cache_group'] ?: sanitize_title( self::$settings['data']['Name'] );
    if( is_multisite() ) $object_cache_group .= '_' . get_current_blog_id();
    $object_cache_expire = self::$settings['object_cache_expire'] ?: 86400; // Default to 24 hours of null

    // Set key variable
    $object_cache_key =  $key . ( is_multisite() ? '_' . get_current_blog_id() : '' );

    // Try to get the value of the cache
    $result = wp_cache_get( $object_cache_key, $object_cache_group );
    if( $result && is_serialized( $result ) ) $result = unserialize($result);

    // If result wasn't found/returned and/or caching is disabled, set & return the value from $callback
    if(true || !$result) {
      $result = $callback();
      if( is_array( $result ) || is_object( $result ) ) $set_result = serialize( $result );
      wp_cache_set( $object_cache_key, $set_result, $object_cache_group, $object_cache_expire);
    }

    return $result;

  }

  /**
    * Flushes the object cache, if enabled. Parameters are not used but are
    *    when passed by 'publish_post' action hook.
    *
    * Example usage: Cache::flush();
    *
    * @param int $ID The ID of the post being published
    * @param WP_Post The post object that is being published
    * @return mixed Returns success as JSON string if called by AJAX,
    *    else true/false
    */
  public static function flush($ID = null, $post = null) {

    $result = array('success' => true);

    try {
      wp_cache_flush();
    } catch (Exception $e) {
      $result = array('success' => false, 'message' => $e->getMessage());
    }

    if( defined('DOING_AJAX') && DOING_AJAX ) {
      echo json_encode($result);
      die();
    }
    return $result['success'];

  }

}
