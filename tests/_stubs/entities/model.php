<?php
// WARNING! THIS IS A GENERATED FILE, PLEASE DO NOT EDIT!

namespace gen\Entities;


use Nuclear\Hierarchy\NodeSourceExtension;

class NsProjecttest extends NodeSourceExtension {

    /**
     * The fillable fields for the model.
     */
    protected $fillable = ['date', 'area', 'location'];

    /*
     * Returns the fields for the model
     */
    public static function getSourceFields()
    {
        return ['date', 'area', 'location'];
    }

}