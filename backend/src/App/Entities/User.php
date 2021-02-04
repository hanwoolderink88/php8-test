<?php

namespace TestingTimes\App\Entities;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use TestingTimes\App\Traits\JsonSerializable;

/**
 * @Entity()
 * @Table(name="users")
 */
class User implements \JsonSerializable
{
    use JsonSerializable;

    /**
     * @Id()
     * @Column(type="integer")
     * @var int
     */
    private int $id;

    /**
     * @Column(type="string")
     */
    private string $name;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return User
     */
    public function setName(string $name): User
    {
        $this->name = $name;

        return $this;
    }
}
