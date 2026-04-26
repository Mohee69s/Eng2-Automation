<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function nodes(): HasMany
    {
        return $this->hasMany(WorkflowNode::class)->orderBy('execution_order');
    }

    public function edges(): HasMany
    {
        return $this->hasMany(WorkflowEdge::class)->orderBy('sort_order');
    }
}
