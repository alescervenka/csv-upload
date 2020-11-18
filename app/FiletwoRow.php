<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FiletwoRow extends Model
{
    protected $fillable = ['record_id', 'record_date', 'event_name', 'number_of_events'];

    protected $dates = ['record_date'];
    /**
     * Get the file1 related to this file2 record
     */
    public function file1()
    {
        return $this->belongsTo('App\FileoneRow', 'record_id', 'record_id');
    }
}
