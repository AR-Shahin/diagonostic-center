<?php

namespace App\Helper\Attribute;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait StatusAttribute
{
    protected function formattedStatus(): Attribute
    {
        return Attribute::make(fn ($status) => $status == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>');
    }

    public function formattedFrontStatus($is_front)
    {
        return $is_front ? '<span class="badge badge-info mx-1">Front - ('.$this->front_order.')</span>' : '';
    }

    public function getStatusBadgeAttribute()
    {
        return $this->status ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
    }

    public function formattedDefaultStatus()
    {
        return $this->is_default ? '<span class="badge badge-primary mx-1">Default</span>' : '';
    }

    public function scopeActive($query)
    {
        $query->whereStatus(1);
    }

    public function getStatusContactAttribute()
    {
        return $this->status ? '<span class="badge badge-success">Read</span>' : '<span class="badge badge-warning">New</span>';
    }
}
