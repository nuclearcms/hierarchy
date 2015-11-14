<?php

namespace Nuclear\Hierarchy\Http\Forms;


use Kris\LaravelFormBuilder\Form;

class NodeSourceForm extends Form {

    public function buildForm()
    {
        $this->add('title', 'text', [
            'rules' => 'required|max:255'
        ]);
        $this->add('node_name', 'slug', [
            'rules' => 'max:255|alpha_dash'
        ]);
    }

}