<?php echo '<?php'; ?>


namespace gen\Forms;


use Kris\LaravelFormBuilder\Form;

class {{ $name }} extends Form {

    public function buildForm()
    {
        $this->compose('Nuclear\Hierarchy\Http\Forms\NodeSourceForm');
        @foreach($fields as $field)
        $this->add('{{ $field->name }}', '{{ $field->type }}', [
            'label' => '{{ $field->label }}',
            'help_block' => ['text' => '{{ $field->description }}'],

            @unless(is_null($field->rules))
            'rules' => {!! $field->rules !!},
            @endunless

            @unless(is_null($field->default_value))
            'default_value' => {!! $field->default_value !!},
            @endunless

            {!! $field->options !!}

        ]);
        @endforeach
    }

}