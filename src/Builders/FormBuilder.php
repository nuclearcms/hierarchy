<?php

namespace Nuclear\Hierarchy\Builders;


use Illuminate\Support\Collection;
use Nuclear\Hierarchy\Contract\Builders\FormBuilderContract;

class FormBuilder implements FormBuilderContract {

    /**
     * Builds a source form
     *
     * @param string $name
     * @param Collection|null $fields
     */
    public function build($name, Collection $fields = null)
    {
        // TODO: Implement build() method.
    }

    /**
     * Destroys a source form
     *
     * @param string $name
     */
    public function destroy($name)
    {
        // TODO: Implement destroy() method.
    }

}