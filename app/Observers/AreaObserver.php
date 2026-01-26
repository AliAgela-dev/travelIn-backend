<?php

namespace App\Observers;

use App\Models\Area;
use Illuminate\Support\Facades\Cache;

class AreaObserver
{
    /**
     * Handle the Area "created" event.
     */
    public function created(Area $area): void
    {
        $this->clearCache($area);
    }

    /**
     * Handle the Area "updated" event.
     */
    public function updated(Area $area): void
    {
        $this->clearCache($area);
    }

    /**
     * Handle the Area "deleted" event.
     */
    public function deleted(Area $area): void
    {
        $this->clearCache($area);
    }

    /**
     * Clear area-related caches.
     */
    private function clearCache(Area $area): void
    {
        Cache::forget('areas_city_' . $area->city_id);
    }
}
