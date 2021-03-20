<?php

namespace Infrastructure\Listeners;

use Illuminate\Support\Arr;
use Infrastructure\Services\FreeSwicthSocketService as FSSocketService;

class ClearFusionPBXCache
{
    public function handle($event)
    {
      $socket = new FSSocketService;

      $clearCacheOptions = Arr::get($event->options, 'clearCacheUri');
      if (!empty($clearCacheOptions))
      {
        $socket->clearCache($clearCacheOptions);
      }

      $socket->reloadXML();
      return true;

      /* My old code example
      require_once dirname(__FILE__) . '/../../../fusionpbx/resources/classes/cache.php';

      //clear the cache
      $cache = new \cache;
      $cache->delete($event->extension->cacheURI);
      */

    }


}