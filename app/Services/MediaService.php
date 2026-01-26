<?php

namespace App\Services;

use App\Models\TemporaryUpload;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaService
{
    /**
     * Move media from a TemporaryUpload model to a target model.
     *
     * @param int|string $tempUploadId
     * @param HasMedia $target
     * @param string $collectionName
     * @return Media|null
     */
    public function moveMediaFromTemp($tempUploadId, HasMedia $target, string $collectionName = 'default')
    {
        $tempUpload = TemporaryUpload::with('media')->find($tempUploadId);

        if (!$tempUpload || $tempUpload->getMedia('default')->isEmpty()) {
            return null;
        }

        $mediaItem = $tempUpload->getFirstMedia('default');

        // Move the media to the new owner
        return $mediaItem->move($target, $collectionName, 'public');
    }
}
