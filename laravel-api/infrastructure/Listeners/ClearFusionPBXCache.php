<?php

namespace Infrastructure\Listeners;

use Infrastructure\Services\FreeSwicthSocketService as FSSocketService;

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
      return true;

      /* My old code example
      require_once dirname(__FILE__) . '/../../../fusionpbx/resources/classes/cache.php';

      //clear the cache
      $cache = new \cache;
      $cache->delete($event->extension->cacheURI);
      */

    }


}