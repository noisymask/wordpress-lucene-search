<?php
/**
 *
 *
 */
class LuceneSearch_Cache
{

    protected static $ttl = 300;

    /**
     * Retrieve from cache
     * @param $key
     * @return bool|mixed
     */
    public static function load($key)
    {
        $key = md5($key);
        if (function_exists('apc_cache_info') && function_exists('apc_exists')) {
            if ($data = apc_fetch($key)) // APC Cache
            {
                return @unserialize($data);
            }
        } else {
            if ($data = get_transient($key)) // WP database cache
            {
                return $data;
            }
        }
        return false;
    }


    /**
     * Save to cache
     * @param $key
     * @param $data
     */
    public static function save($key, $data)
    {
        $ttl = defined('LUCENE_CACHE_TTL') ? LUCENE_CACHE_TTL : self::$ttl;
        $key = md5($key);
        self::delete($key);
        if (function_exists('apc_cache_info') && function_exists('apc_store')) {
            $data = @serialize($data);
            apc_store($key, $data, $ttl);
        } else {
            set_transient($key, $data, $ttl);
        }
    }


    /**
     * Delete from cache
     * @param $key
     */
    public static function delete($key)
    {
        $key = md5($key);
        if (function_exists('apc_cache_info') && function_exists('apc_exists') && function_exists('apc_delete')) {
            if (apc_exists($key)) {
                @apc_delete($key);
            }
        }
        delete_transient($key);
    }

}