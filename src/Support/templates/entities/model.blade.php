<?php echo '<?php'; ?>

// WARNING! THIS IS A GENERATED FILE, PLEASE DO NOT EDIT!

namespace gen\Entities;


use Nuclear\Hierarchy\NodeSourceExtension;

class {{ $name }} extends NodeSourceExtension {

    /**
     * The fillable fields for the model.
     */
    protected $fillable = [{!! $fields !!}];

    /**
     * Returns the fields for the model
     */
    public static function getSourceFields()
    {
        return [{!! $fields !!}];
    }

    /**
     * Returns searchables for the model
     */
    public static function getSearchableFields()
    {
        return [
            'columns' => [{!! $searchableFields !!}],
            'joins' => [
                '{{ $tableName }}' => ['nodes.id', '{{ $tableName }}.node_id'],
            ]
        ];
    }

}