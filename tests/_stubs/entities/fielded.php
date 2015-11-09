<?php

namespace gen\Forms;


use Kris\LaravelFormBuilder\Form;

class CreateEditProjectForm extends Form {

    public function buildForm()
    {
        $this->compose('Nuclear\Hierarchy\Http\Forms\NodeSourceForm');
                $this->add('description', 'text', [
            'label' => 'Project Description',
            'help_block' => ['text' => 'Some hints'],

                        'rules' => 'required|max:5000',

                        'default_value' => 'Texty text',
            ]);
                $this->add('type', 'select', [
            'label' => 'Project Type',
            'help_block' => ['text' => 'Some hints for type'],
                                    'choices' => [1 => 'Housing', 2 => 'Cultural'], 'selected' => function($data) {return 1;}, 'empty_value' => '---no type---'
        ]);
    }

}