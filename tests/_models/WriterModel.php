<?php

use Nuclear\Hierarchy\Builders\Writer;
use Nuclear\Hierarchy\Contract\Builders\WriterContract;

class WriterModel implements WriterContract {

    use Writer;


    /**
     * Getter for entity base path
     *
     * @return string
     */
    public function getBasePath()
    {
        return generated_path();
    }

}