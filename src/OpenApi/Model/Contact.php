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

namespace ApiScout\OpenApi\Model;

final class Contact
{
    public function __construct(
        private ?string $name = null,
        private ?string $url = null,
        private ?string $email = null,
    ) {
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
}
