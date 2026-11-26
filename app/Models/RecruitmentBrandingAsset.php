<?php

namespace App\Models;

class RecruitmentBrandingAsset extends TenantModel
{
    protected $fillable = ['company_id', 'kind', 'name', 'mime_type', 'sha256', 'image_data'];

    protected $hidden = ['image_data'];

    public function imageDataUri(): string
    {
        return 'data:'.$this->mime_type.';base64,'.$this->image_data;
    }
}
