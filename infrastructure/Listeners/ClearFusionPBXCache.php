<?php

namespace Infrastructure\Listeners;

use App\Services\FreeSwicthSocketService as FSSocketService;

class ClearFusionPBXCache
{
    public function handle($event)
    {

      $socket = new FSSocketService;

      if (!empty($event->clearCacheUri))
      {
        $socket->clearCache($event->clearCacheUri);
      }

      $socket->reloadXML();

      /* My old code example
      require_once dirname(__FILE__) . '/../../../fusionpbx/resources/classes/cache.php';

      //clear the cache
      $cache = new \cache;
      $cache->delete($event->extension->cacheURI);
      */

    }


}