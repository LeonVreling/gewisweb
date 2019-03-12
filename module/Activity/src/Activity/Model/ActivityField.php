<?php

namespace Activity\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Activity field model.
 *
 * @ORM\Entity
 */
class ActivityField
{
    /**
     * ID for the field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * Activity that the field belongs to.
     *
     * @ORM\ManyToOne(targetEntity="Activity\Model\Activity", inversedBy="fields", cascade={"persist"})
     * @ORM\JoinColumn(name="activity_id",referencedColumnName="id")
     */
    protected $activity;

    /**
     * The name of the field.
     *
     * @ORM\OneToOne(targetEntity="Activity\Model\LocalisedText", orphanRemoval=true)
     */
    protected $name;


    /**
     * The type of the field.
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $type;

    /**
     * The minimal value constraint for the ``number'' type
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $minimumValue;

    /**
     * The maximal value constraint for the ``number'' type.
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $maximumValue;

    /**
     * The allowed options for the field of the ``option'' type.
     *
     * @ORM\OneToMany(targetEntity="ActivityOption", mappedBy="field")
     */
    protected $options;

    public function setActivity($activity)
    {
        $this->activity = $activity;
    }

    /**
     * @param LocalisedText $name
     */
    public function setName($name)
    {
        $this->name = $name->copy();
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setMinimumValue($minimumValue)
    {
        $this->minimumValue = $minimumValue;
    }

    public function setMaximumValue($maximumValue)
    {
        $this->maximumValue = $maximumValue;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return LocalisedText
     */
    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getMinimumValue()
    {
        return $this->minimumValue;
    }

    public function getMaximumValue()
    {
        return $this->maximumValue;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * Returns an associative array representation of this object.
     *
     * @return array
     */
    public function toArray()
    {
        $options = [];
        foreach ($this->getOptions() as $option) {
            $options[] = $option->toArray();
        }

        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'type' => $this->getType(),
            'minimumValue' => $this->getMinimumValue(),
            'maximumValue' => $this->getMaximumValue(),
            'options' => $options
        ];
    }
}
