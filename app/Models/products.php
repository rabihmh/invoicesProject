<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class products extends Model
{
    use HasFactory;

    //protected $fillable = ['Products_name', 'description', 'section_id'];
    protected $guarded = [];

    public function sections()
    {
        return $this->belongsTo('App\Models\sections');
    }
}
