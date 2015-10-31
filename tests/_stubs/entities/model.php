<?php

namespace gen\Entities;


use Illuminate\Database\Eloquent\Model as Eloquent;

class NsProject extends Eloquent {

    /**
     * The fillable fields for the model.
     */
    protected $fillable = ['date', 'area', 'location'];

    /*
     * Timestamps for the model.
     */
    public $timestamps = false;

}