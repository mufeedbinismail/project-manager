<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AttributeValue
 *
 * Represents the value of an attribute for a specific entity (project).
 *
 * @package App\Models
 */
class AttributeValue extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'attribute_id',
        'attribute_name',
        'attribute_type', 
        'entity_id',
        'value',
        'value_description'
    ];

    /**
     * Get the attribute that owns the value.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}
