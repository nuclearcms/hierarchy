<?php

namespace Nuclear\Hierarchy\Contract\Builders;


use Illuminate\Support\Collection;

interface FormBuilderContract {

    /**
     * Builds a source form
     *
     * @param string $name
     * @param Collection|null $fields
     */
    public function build($name, Collection $fields = null);

    /**
     * Destroys a source form
     *
     * @param string $name
     */
    public function destroy($name);

}