<?php
namespace app\common\config;

class Confg
{
    const OATH_CACHE_NAME = 'oath_cache_name';
    const HOTEL_OATH_CACHE_NAME = 'hotel_oath_cache_name';
    const AGENT_OATH_CACHE_NAME = 'agent_oath_cache_name';

    public static function getOathCacheName($user_id)
    {
        return static::OATH_CACHE_NAME . '_' . $user_id;
    }


    public static function getHotelOathCacheName($user_id)
    {
        return static::HOTEL_OATH_CACHE_NAME . '_' . $user_id;
    }

    public static function getAgentOathCacheName($user_id)
    {
        return static::AGENT_OATH_CACHE_NAME . '_' . $user_id;
    }
}