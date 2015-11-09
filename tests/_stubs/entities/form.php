<?php

namespace gen\Forms;


use Kris\LaravelFormBuilder\Form;

class CreateEditCategoryForm extends Form {

    public function buildForm()
    {
        $this->compose('Nuclear\Hierarchy\Http\Forms\NodeSourceForm');
            }

}