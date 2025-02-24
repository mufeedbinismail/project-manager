<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Attribute
 *
 * Represents an attribute in the EAV (Entity-Attribute-Value) system.
 *
 * @package App\Models
 */
class Attribute extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_SELECT = 'select';
    const TYPE_TEXT = 'text';
    const TYPE_DATE = 'date';
    const TYPE_NUMBER = 'number';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'type'];

    /**
     * Get the possible types that this attribute can be.
     *
     * @return array
     */
    public static function possibleTypes()
    {
        return [
            static::TYPE_DATE,
            static::TYPE_NUMBER,
            static::TYPE_SELECT,
            static::TYPE_TEXT
        ];
    }

    /**
     * Get the possible values associated with this attribute.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function possibleValues()
    {
        return $this->hasMany(AttributePossibleValue::class);
    }

    /**
     * Get the attribute values associated with this attribute.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }

    /**
     * Get the validation rule for the attribute.
     *
     * @return string|string[]|\Illuminate\Contracts\Validation\Rule[]|mixed
     */
    public function getValidationRules()
    {
        switch ($this->type) {
            case self::TYPE_TEXT:
                return 'required|string|max:255';
            case self::TYPE_NUMBER:
                return 'required|numeric';
            case self::TYPE_DATE:
                return 'required|date_format:Y-m-d';
            case self::TYPE_SELECT:
                return 'required|exists:attribute_possible_values,key,attribute_id,' . $this->id;
            default:
                return 'required|string|max:255';
        }
    }
}
