<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitPhotoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'visit_report_id' => $this->visit_report_id,
            'uploaded_by' => $this->uploaded_by,
            'uploader' => new UserResource($this->whenLoaded('uploader')),
            'file_path' => $this->file_path,
            'url' => $this->url,
            'original_name' => $this->original_name,
            'photo_type' => $this->photo_type,
            'caption' => $this->caption,
            'created_at' => $this->created_at,
        ];
    }
}
