<?php
// WARNING! THIS IS A GENERATED FILE, PLEASE DO NOT EDIT!

namespace gen\Forms;


use Kris\LaravelFormBuilder\Form;
use Nuclear\Hierarchy\Http\Forms\NodeSourceForm;

class EditCategoryForm extends Form {

    public function buildForm()
    {
        $this->compose(new NodeSourceForm);
            }

}