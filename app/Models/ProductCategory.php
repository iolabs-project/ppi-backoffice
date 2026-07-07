<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $fillable = [
        'company_id',
        'code',
        'name',
        'description',
        'created_by',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
