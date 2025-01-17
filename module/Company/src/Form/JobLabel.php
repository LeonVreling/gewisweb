<?php

namespace Company\Form;

use Application\Form\Localisable as LocalisableForm;
use Laminas\Mvc\I18n\Translator;
use Laminas\Filter\{
    StringTrim,
    StripTags,
};
use Laminas\Form\Element\{
    Submit,
    Text,
};
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\StringLength;

class JobLabel extends LocalisableForm implements InputFilterProviderInterface
{
    /**
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        // we want to ignore the name passed
        parent::__construct($translator);
        $this->setAttribute('method', 'post');

        // All language attributes.
        $this->add(
            [
                'name' => 'name',
                'type' => Text::class,
                'options' => [
                    'label' => $this->getTranslator()->translate('Name'),
                ],
            ]
        );

        $this->add(
            [
                'name' => 'nameEn',
                'type' => Text::class,
                'options' => [
                    'label' => $this->getTranslator()->translate('Name'),
                ],
            ]
        );

        $this->add(
            [
                'name' => 'abbreviation',
                'type' => Text::class,
                'options' => [
                    'label' => $this->getTranslator()->translate('Abbreviation'),
                ],
            ]
        );

        $this->add(
            [
                'name' => 'abbreviationEn',
                'type' => Text::class,
                'options' => [
                    'label' => $this->getTranslator()->translate('Abbreviation'),
                ],
            ]
        );

        $this->add(
            [
                'name' => 'submit',
                'type' => Submit::class,
            ]
        );
    }


    /**
     * @inheritDoc
     */
    protected function createLocalisedInputFilterSpecification(string $suffix = ''): array
    {
        return [
            'name' . $suffix => [
                'required' => true,
                'validators' => [
                    [
                        'name' => StringLength::class,
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min' => 2,
                            'max' => 127,
                        ],
                    ],
                ],
                'filters' => [
                    [
                        'name' => StripTags::class,
                    ],
                    [
                        'name' => StringTrim::class,
                    ],
                ],
            ],
            'abbreviation' . $suffix => [
                'required' => true,
                'validators' => [
                    [
                        'name' => StringLength::class,
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min' => 2,
                            'max' => 127,
                        ],
                    ],
                ],
                'filters' => [
                    [
                        'name' => StripTags::class,
                    ],
                    [
                        'name' => StringTrim::class,
                    ],
                ],
            ],
        ];
    }
}
