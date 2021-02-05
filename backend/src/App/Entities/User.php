<?php

namespace TestingTimes\App\Entities;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
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
     * @GeneratedValue(strategy="UUID")
     * @Column(type="string")
     * @var int
     */
    private string $id;

    /**
     * @Column(type="string")
     */
    private string $name;

    /**
     * @return string
     */
    public function getId(): string
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
