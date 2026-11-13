<?php

namespace App\Helper\Attribute;

trait StatusAttribute
{
    public function getStatusBadgeAttribute()
    {
        return $this->status ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
    }

    public function scopeActive($query)
    {
        $query->whereStatus(1);
    }
}
