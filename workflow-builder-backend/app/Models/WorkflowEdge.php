<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class WorkflowEdge extends Model
{
    protected $fillable = [
        'workflow_id',
        'from_node_id',
        'to_node_id',
        'sort_order',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }
}
