<?php

namespace SugoiCloud\Common;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionFunctionAbstract;
use ReflectionMethod;

/**
 * Utilities for reflection and code introspection.
 */
class Reflect
{
    /**
     * @template T
     *
     * @param ReflectionFunctionAbstract $reflect
     * @param class-string<T> $attribute
     * @param bool $inheritors
     * @return T|null
     */
    public static function attr(\Reflector $reflect, string $attribute, bool $inheritors = true): object|null
    {
        return ($reflect->getAttributes($attribute, $inheritors ? ReflectionAttribute::IS_INSTANCEOF : 0)[0] ?? null)?->newInstance();
    }

    /**
     * @template T
     *
     * @param ReflectionFunctionAbstract $reflect
     * @param class-string<T> $attribute
     * @param bool $inheritors
     * @return array<T>
     */
    public static function attrs(\Reflector $reflect, string $attribute, bool $inheritors = true): array
    {
        return array_map(
            fn($class) => $class->newInstance(),
            $reflect->getAttributes($attribute, $inheritors ? ReflectionAttribute::IS_INSTANCEOF : 0)
        );
    }

    /**
     * @param ReflectionFunctionAbstract $reflect
     * @param class-string $attribute
     * @param bool $inheritors
     * @return bool
     */
    public static function hasAttr(\Reflector $reflect, string $attribute, bool $inheritors = true): bool
    {
        return ($reflect->getAttributes($attribute, $inheritors ? ReflectionAttribute::IS_INSTANCEOF : 0)[0] ?? null) !== null;
    }

    /**
     * @param ReflectionClass $reflect
     * @return array<ReflectionMethod>
     */
    public static function methods(\ReflectionClass $reflect): array
    {
        return $reflect->getMethods(ReflectionMethod::IS_PUBLIC);
    }
}