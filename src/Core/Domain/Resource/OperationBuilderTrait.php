<?php

declare(strict_types=1);

/*
 * This file is part of the ApiScout project.
 *
 * Copyright (c) 2023 ApiScout
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiScout\Core\Domain\Resource;

use ApiScout\Core\Domain\Attribute\ApiProperty;
use ApiScout\Core\Domain\Operation;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

trait OperationBuilderTrait
{
    private function isOperationResource(ReflectionMethod $reflection): bool
    {
        if ($reflection->getAttributes(Operation::class, ReflectionAttribute::IS_INSTANCEOF) !== []) {
            return true;
        }

        return false;
    }

    private function buildOperationFromMethod(ReflectionMethod $method, string $controller): Operation
    {
        $operation = $this->buildMethodOperation($method);

        if ($operation->getFilters() === []
            && ($payload = $this->isPayloadResource($method->getParameters())) !== null) {
            $operation->setFilters(
                $this->buildParameterFilters($payload)
            );
            /** @phpstan-ignore-next-line MapRequestPayload type should never be null */
            $operation->setInput($payload->getType()->getName());
        }

        if ($method->getReturnType() !== null && $operation->getOutput() === null) {
            /** @phpstan-ignore-next-line getName is an existing method */
            $operation->setOutput($method->getReturnType()->getName());
        }

        if ($operation->getUriVariables() === []) {
            $operation->setUriVariables(
                $this->buildUriVariables($method->getParameters(), $operation->getPath())
            );
        }

        $operation->setController($controller);
        $operation->setControllerMethod($method->getName());

        return $operation;
    }

    /**
     * @return array<ApiProperty>
     */
    private function buildParameterFilters(ReflectionParameter $parameter): array
    {
        $apiProperties = [];
        $inputClassName = $parameter->getType();

        if (!$inputClassName instanceof ReflectionNamedType) {
            return $apiProperties;
        }

        /** @phpstan-ignore-next-line parameter 1 is a class-string */
        $queryClass = new ReflectionClass($inputClassName->getName());

        foreach ($queryClass->getProperties() as $property) {
            $apiProperty = $this->buildApiProperty(
                $property->getAttributes()
            );
            if ($apiProperty === null) {
                $apiProperty = new ApiProperty(
                    name: $property->getName(),
                    /** @phpstan-ignore-next-line getName will exist if getType is a ReflectionNamedType */
                    type: !$property->getType() instanceof ReflectionNamedType ? $property->getType()->getName() : 'string',
                    required: $property->getType() !== null ? $property->getType()->allowsNull() : true
                );
            }

            $apiProperties[$property->getName()] = $apiProperty;
        }

        return $apiProperties;
    }

    private function buildMethodOperation(ReflectionMethod $method): Operation
    {
        /**
         * @var ReflectionAttribute $methodAttribute
         */
        $methodAttribute = $method->getAttributes(Operation::class, ReflectionAttribute::IS_INSTANCEOF)[0];

        /** @var Operation */
        return $methodAttribute->newInstance();
    }

    /**
     * @param array<int, ReflectionParameter> $reflectionParameters
     */
    private function isPayloadResource(array $reflectionParameters): ?ReflectionParameter
    {
        foreach ($reflectionParameters as $reflectionParameter) {
            if ($reflectionParameter->getAttributes(MapQueryString::class, ReflectionAttribute::IS_INSTANCEOF) !== []) {
                return $reflectionParameter;
            }

            if ($reflectionParameter->getAttributes(MapRequestPayload::class, ReflectionAttribute::IS_INSTANCEOF) !== []) {
                return $reflectionParameter;
            }
        }

        return null;
    }

    /**
     * @param array<ReflectionAttribute> $propertyAttributes
     */
    private function buildApiProperty(array $propertyAttributes): ?ApiProperty
    {
        foreach ($propertyAttributes as $attribute) {
            if ($attribute->getName() === ApiProperty::class) {
                /**
                 * @var ApiProperty
                 */
                return $attribute->newInstance();
            }
        }

        return null;
    }

    /**
     * @param array<ReflectionParameter> $parameters
     *
     * @return array<string, string>
     */
    private function buildUriVariables(array $parameters, string $path): array
    {
        $parsedPathQuery = explode('/', $path);
        $uriVariables = [];

        foreach ($parameters as $parameter) {
            foreach ($parsedPathQuery as $query) {
                if ($this->isQueryResource($parameter, $query) === true) {
                    /** @phpstan-ignore-next-line getName is an existing method */
                    $uriVariables[$parameter->getName()] = $parameter->getType() !== null ? $parameter->getType()->getName() : 'string';
                }
            }
        }

        return $uriVariables;
    }

    private function isQueryResource(ReflectionParameter $parameter, string $query): bool
    {
        if ($this->isPayloadResource([$parameter]) !== null) {
            return false;
        }

        if (strcmp($parameter->getName(), str_replace(['{', '}'], '', $query)) === 0) {
            return true;
        }

        return false;
    }
}
