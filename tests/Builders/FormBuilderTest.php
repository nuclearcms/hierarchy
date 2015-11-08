<?php

use Nuclear\Hierarchy\Builders\FormBuilder;
use org\bovigo\vfs\vfsStream;

class FormBuilderTest extends TestBase {

    protected function getBuilder()
    {
        return new FormBuilder;
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