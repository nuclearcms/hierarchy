<?php

namespace gen\Forms;


use Kris\LaravelFormBuilder\Form;

class EditCategoryForm extends Form {

    public function buildForm()
    {
        $this->compose(new \Nuclear\Hierarchy\Http\Forms\NodeSourceForm);
            }

}