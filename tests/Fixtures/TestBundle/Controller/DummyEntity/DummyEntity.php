<?php

/*
 * This file is part of the ApiScout project.
 *
 * Copyright (c) 2023 ApiScout
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ApiScout\Tests\Fixtures\TestBundle\Controller\DummyEntity;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Marvin Courcier <marvincourcier.dev@gmail.com>
 */
final class DummyEntity
{
    public function __construct(
        #[Groups(['dummy::read', 'dummy::write'])]
        public string $firstName,
        #[Groups(['dummy::read', 'dummy::write'])]
        #[Assert\NotBlank()]
        public string $lastName,
        #[Groups(['dummy::read', 'dummy::write'])]
        public ?DummyCompanyEntity $addressEntity,
        #[Groups(['dummy::toto'])]
        public int $id = 1,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }
}
