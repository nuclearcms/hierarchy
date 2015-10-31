<?php echo '<?php'; ?>


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

}