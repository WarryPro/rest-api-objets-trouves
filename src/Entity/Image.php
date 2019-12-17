<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ImageRepository")
 */
class Image implements \JsonSerializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Item", inversedBy="images", cascade={"remove"})
     */
    private $Item;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getItem(): ?item
    {
        return $this->Item;
    }

    public function setItem(?item $Item)
    {
        $this->Item = $Item;

        return $this;
    }

    public function jsonSerialize():array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'items' => $this->Item,
        ];
    }
}
