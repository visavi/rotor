<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin File */
class FileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'path'      => url($this->getUrl()),
            'size'      => $this->size,
            'extension' => $this->extension,
            'mime_type' => $this->mime_type,
            'is_image'  => $this->isImage(),
            'is_audio'  => $this->isAudio(),
            'is_video'  => $this->isVideo(),
        ];
    }
}
