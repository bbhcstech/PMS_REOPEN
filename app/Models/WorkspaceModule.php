<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Company-local module configuration; never writes to the platform registry. */
class WorkspaceModule extends Module
{
    protected $connection = 'tenant';
    protected $table = 'modules';

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
