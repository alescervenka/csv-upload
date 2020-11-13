<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FileoneRow extends Model
{
    protected $fillable = ['record_id', 'record_name'];
    protected $primaryKey = 'record_id';
    public $incrementing = false;
    protected $keyType = 'string';
}
