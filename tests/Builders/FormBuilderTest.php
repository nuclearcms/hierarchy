<?php

use Illuminate\Database\Eloquent\Collection;
use Nuclear\Hierarchy\Builders\FormBuilder;
use Nuclear\Hierarchy\NodeField;
use org\bovigo\vfs\vfsStream;

class FormBuilderTest extends TestBase {

    protected function getBuilder()
    {
        return new FormBuilder;
    }

    /** @test */
    function it_creates_a_form()
    {
        $builder = $this->getBuilder();

        $this->assertFileNotExists(
            $builder->getClassFilePath('category')
        );

        $builder->build('category');

        $this->assertFileExists(
            $builder->getClassFilePath('category')
        );

        $this->assertFileEquals(
            $builder->getClassFilePath('category'),
            dirname(__DIR__) . '/_stubs/entities/form.php'
        );
    }

    /** @test */
    function it_creates_a_form_with_fields()
    {
        $builder = $this->getBuilder();

        $this->assertFileNotExists(
            $builder->getClassFilePath('project')
        );

        $builder->build('project', Collection::make([
            NodeField::create([
                'name' => 'description',
                'type' => 'text',
                'description' => 'Some hints',
                'label' => 'Project Description',
                'rules' => '\'required|max:5000\'',
                'default_value' => '\'Texty text\'',
                'position' => 1
            ]),
            NodeField::create([
                'name' => 'type',
                'type' => 'select',
                'description' => 'Some hints for type',
                'label' => 'Project Type',
                'position' => 2,
                'options' => "'choices' => [1 => 'Housing', 2 => 'Cultural'], 'selected' => function(\$data) {return 1;}, 'empty_value' => '---no type---'"
            ])
        ]));

        $this->assertFileExists(
            $builder->getClassFilePath('project')
        );
    }

    /** @test */
    function it_destroys_a_form()
    {
        $builder = $this->getBuilder();

        $builder->build('project');

        $this->assertFileExists(
            $builder->getClassFilePath('project')
        );

        $builder->destroy('project');

        $this->assertFileNotExists(
            $builder->getClassFilePath('project')
        );
    }

    /** @test */
    function it_returns_the_class_name()
    {
        $builder = $this->getBuilder();

        $this->assertEquals(
            'CreateEditProjectForm',
            $builder->getClassName('project')
        );
    }

    /** @test */
    function it_returns_forms_path()
    {
        $builder = $this->getBuilder();

        $this->assertEquals(
            vfsStream::url('gen/Forms'),
            $builder->getBasePath()
        );
    }

    /** @test */
    function it_returns_the_class_path()
    {
        $builder = $this->getBuilder();

        $this->assertEquals(
            generated_path() . '/Forms/CreateEditProjectForm.php',
            $builder->getClassFilePath('project')
        );
    }
}