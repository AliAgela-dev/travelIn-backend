<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Upload\StoreUploadRequest;
use App\Models\TemporaryUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function store(StoreUploadRequest $request)
    {

        $temporaryUpload = TemporaryUpload::create([
            'session_id' => $request->user()?->id ?? Str::uuid(),
        ]);

        $media = $temporaryUpload->addMediaFromRequest('file')
            ->toMediaCollection('default');

        return $this->success([
            'temp_id' => $temporaryUpload->id,
            'url' => $media->getUrl(),
            'name' => $media->file_name,
            'mime_type' => $media->mime_type,
        ], 'File uploaded successfully.');
    }
}
