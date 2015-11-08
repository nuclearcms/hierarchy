<?php

namespace Nuclear\Hierarchy\Http\Forms;


class NodeSourceForm extends Form {

    public function buildForm()
    {
        $this->add('title', 'text', [
            'rules' => 'required|max:255'
        ]);
        $this->add('slug', 'slug', [
            'rules' => 'max:255|alpha_dash'
        ]);
    }

}