<?php

namespace Frontpage\Form;

use Laminas\Form\Element\{
    Submit,
    Text,
    Textarea,
};
use Laminas\Form\Form;
use Laminas\Mvc\I18n\Translator;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\StringLength;

class PollComment extends Form implements InputFilterProviderInterface
{
    /**
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        parent::__construct();

        $this->add(
            [
                'name' => 'author',
                'type' => Text::class,
                'options' => [
                    'label' => $translator->translate('Author'),
                ],
            ]
        );

        $this->add(
            [
                'name' => 'content',
                'type' => Textarea::class,
                'options' => [
                    'label' => $translator->translate('Content'),
                ],
            ]
        );

        $this->add(
            [
                'name' => 'submit',
                'type' => Submit::class,
                'attributes' => [
                    'value' => $translator->translate('Comment'),
                ],
            ]
        );
    }

    /**
     * Should return an array specification compatible with
     * {@link \Laminas\InputFilter\Factory::createInputFilter()}.
     *
     * @return array
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'author' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => StringLength::class,
                        'options' => [
                            'min' => 2,
                            'max' => 32,
                        ],
                    ],
                ],
            ],
            'content' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => StringLength::class,
                        'options' => [
                            'min' => 8,
                        ],
                    ],
                ],
            ],
        ];
    }
}
