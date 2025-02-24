<?php

namespace App\Models;

use App\Traits\HasEntityAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes, HasEntityAttributes;

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['entityAttributes'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'status',
    ];

    /**
     * Get the list of possible statuses for a project.
     *
     * @return array
     */
    public static function getStatuses() {
        return ['active', 'inactive', 'completed'];
    }

    /**
     * The users that are assigned to this project.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * The timesheets that belong to the project.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function timesheets()
    {
        return $this->hasMany(Timesheet::class);
    }

    /**
     * The dynamic attributes of this project.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attributes()
    {
        return $this->hasMany(AttributeValue::class, 'entity_id');
    }
}
