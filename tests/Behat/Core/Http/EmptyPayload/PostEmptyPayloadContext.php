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

namespace ApiScout\Tests\Behat\Core\Http\EmptyPayload;

use ApiScout\HttpOperation;
use ApiScout\Tests\Behat\Core\Http\BaseContext;
use Behat\Gherkin\Node\PyStringNode;
use PHPUnit\Framework\Assert;

final class PostEmptyPayloadContext extends BaseContext
{
    private const POST_EMPTY_PAYLOAD_PATH = 'empty/payload';

    /**
     * @When one post an empty payload
     */
    public function when(): void
    {
        $this->request(
            HttpOperation::METHOD_POST,
            self::POST_EMPTY_PAYLOAD_PATH,
        );
    }

    /**
     * @Then post with empty payload response should have:
     */
    public function then(PyStringNode $content): void
    {
        $content = $this->json($content->getRaw());

        Assert::assertSame(
            $content,
            $this->getResponse()->toArray(false)
        );
    }
}
