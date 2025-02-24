<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AttributePossibleValue
 *
 * Represents a possible value for an attribute.
 *
 * @package App\Models
 */
class AttributePossibleValue extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['attribute_id', 'key', 'value'];

    /**
     * Get the attribute that owns the possible value.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}
