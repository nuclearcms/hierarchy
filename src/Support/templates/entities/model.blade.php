<?php echo '<?php'; ?>

// WARNING! THIS IS A GENERATED FILE, PLEASE DO NOT EDIT!

namespace gen\Entities;


use Illuminate\Database\Eloquent\Model as Eloquent;

class {{ $name }} extends Eloquent {

    /**
     * The fillable fields for the model.
     */
    protected $fillable = [{!! $fields !!}];

    /*
     * Timestamps for the model.
     */
    public $timestamps = false;

    /*
     * Returns the fields for the model
     */
    public static function getSourceFields()
    {
        return [{!! $fields !!}];
    }

}