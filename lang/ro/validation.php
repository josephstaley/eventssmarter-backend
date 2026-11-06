<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => ':attribute трябва да се приеме.',
    'active_url'           => ':attribute не е валиден URL адрес.',
    'after'                => ':attribute трябва да е дата след :date.',
    'after_or_equal'       => ':attribute трябва да е дата след или равна на :date.',
    'alpha'                => ':attribute може да съдържа само букви.',
    'alpha_dash'           => ':attribute може да съдържа само букви, цифри и тирета.',
    'alpha_num'            => ':attribute може да съдържа само букви и цифри.',
    'array'                => ':attribute трябва да е масив.',
    'before'               => ':attribute трябва да е дата преди :date.',
    'before_or_equal'      => ':attribute трябва да е дата преди или равна на :date.',
    'between'              => [
        'numeric' => ':attribute трябва да бъде между :min и :max.',
        'file'    => ':attribute трябва да бъде между :min и :max килобайта.',
        'string'  => ':attribute трябва да бъде между :min и :max знаци.',
        'array'   => ':attribute трябва да има между :min и :max елементи.',
    ],
    'boolean'              => ':attribute полето трябва да е вярно или невярно.',
    'confirmed'            => ':attribute потвърждението не съвпада.',
    'date'                 => ':attribute не е валидна дата.',
    'date_format'          => ':attribute не съответства на формата :format.',
    'different'            => ':attribute и :other трябва да са различни.',
    'digits'               => ':attribute трябва да бъде :digits цифри.',
    'digits_between'       => ':attribute трябва да е между :min и :max цифри.',
    'dimensions'           => ':attribute има невалидни размери на изображението.',
    'distinct'             => 'Полето :attribute има дублирана стойност.',
    'email'                => ':attribute трябва да е валиден имейл адрес.',
    'exists'               => 'избрано: атрибутът е невалиден.',
    'file'                 => ':attribute трябва да е файл.',
    'filled'               => 'Полето :attribute трябва да има стойност.',
    'image'                => ':attribute трябва да е изображение.',
    'in'                   => 'избрани :attribute е невалиден.',
    'in_array'             => 'Полето :attribute не съществува в :other.',
    'integer'              => ':attribute трябва да е цяло число.',
    'ip'                   => ':attribute трябва да е валиден IP адрес.',
    'ipv4'                 => ':attribute трябва да е валиден IPv4 адрес.',
    'ipv6'                 => ':attribute трябва да е валиден IPv6 адрес.',
    'json'                 => ':attribute трябва да е валиден JSON низ.',
    'max'                  => [
        'numeric' => ':attribute не може да бъде по-голямо от :max.',
        'file'    => ':attribute не може да бъде по-голям от :max килобайта.',
        'string'  => ':attribute не може да бъде по-голям от :max знака.',
        'array'   => ':attribute не може да има повече от :max елементи.',
    ],
    'mimes'                => ':attribute трябва да е файл от тип: :values.',
    'mimetypes'            => ':attribute трябва да е файл от тип: :values.',
    'min'                  => [
        'numeric' => ':attribute трябва да бъде поне :min.',
        'file'    => ':attribute трябва да бъде поне :min килобайта.',
        'string'  => ':attribute трябва да съдържа поне :min знака.',
        'array'   => ':attribute трябва да има поне :min елементи.',
    ],
    'not_in'               => 'избрани :attribute е невалиден.',
    'numeric'              => ':attribute трябва да е число.',
    'present'              => 'Трябва да присъства полето :attribute.',
    'regex'                => 'Форматът на :attribute е невалиден.',
    'required'             => ':attribute задължително поле.',
    'required_if'          => 'Полето :attribute е задължително, когато :other е :value.',
    'required_unless'      => 'Полето :attribute е задължително, освен ако :other е в :values.',
    'required_with'        => 'Полето :attribute е задължително, когато присъства :values.',
    'required_with_all'    => 'Полето :attribute е задължително, когато присъства :values.',
    'required_without'     => 'Полето :attribute е задължително, когато :values не присъства.',
    'required_without_all' => 'Полето :attribute е задължително, когато не присъства нито една от :values.',
    'same'                 => ':attribute и :other трябва да съвпадат.',
    'size'                 => [
        'numeric' => ':attribute трябва да бъде :size.',
        'file'    => ':attribute трябва да бъде :size килобайта.',
        'string'  => ':attribute трябва да бъде :size знаци.',
        'array'   => ':attribute трябва да съдържа :size елементи.',
    ],
    'string'               => ':attribute трябва да е низ.',
    'timezone'             => ':attribute трябва да е валидна зона.',
    'unique'               => ':attribute вече е заето.',
    'uploaded'             => ':attribute не успя да качи.',
    'url'                  => 'Форматът на :attribute е невалиден.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'starts_at.date' => [
            'required' => 'Изисква се начална дата на събитието.',
        ],
        'starts_at.time' => [
            'required' => 'Изисква се начален час на събитието.',
        ],
        'ends_at.date' => [
            'required' => 'Крайната дата на събитието е задължителна.',
        ],
        'ends_at.time' => [
            'required' => 'Изисква се краен час на събитието.',
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],

];
