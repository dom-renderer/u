<?php

namespace App\Models;

use App\Traits\ContentEllipse;
use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    use ContentEllipse;

    public $table = 'ticketit_settings';

    public $fillable = [
    'lang',
    'slug',
    'value',
    'default',
  ];

    // Null lang column if no value is being stored.

    public function setLangAttribute($lang)
    {
        $this->attributes['lang'] = trim($lang) !== '' ? $lang : null;
    }

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
    'id'         => 'integer',
    'lang'       => 'string',
    'slug'       => 'string',
    'value'      => 'string',
    'default'    => 'string',
  ];

    public static $rules = [

  ];
}
