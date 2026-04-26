<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class WorkflowNode extends Model
{
    protected $fillable = [
        'workflow_id',
        'node_id',
        'type',
        'position_x',
        'position_y',
        'text',
        'color',
        'execution_order',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }
}
