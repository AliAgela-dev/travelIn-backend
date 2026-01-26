<?php

namespace App\Observers;

use App\Models\City;
use Illuminate\Support\Facades\Cache;

class CityObserver
{
    /**
     * Handle the City "created" event.
     */
    public function created(City $city): void
    {
        $this->clearCache();
    }

    /**
     * Handle the City "updated" event.
     */
    public function updated(City $city): void
    {
        $this->clearCache();
    }

    /**
     * Handle the City "deleted" event.
     */
    public function deleted(City $city): void
    {
        $this->clearCache();
    }

    /**
     * Clear city-related caches.
     */
    private function clearCache(): void
    {
        Cache::forget('cities_list');
    }
}
