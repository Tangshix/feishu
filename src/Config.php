<?php

declare(strict_types=1);

namespace Larafly\Feishu;

use ArrayAccess;
use Larafly\Feishu\Contracts\Config as ConfigInterface;
use Larafly\Feishu\Exceptions\InvalidArgumentException;
use Illuminate\Support\Arr;

/**
 * @implements ArrayAccess<mixed, mixed>
 */
class Config implements ArrayAccess, ConfigInterface
{
    /**
     * @var array<string>
     */
    protected array $requiredKeys = [];

    /**
     * @param  array<string, mixed>  $items
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        protected array $items = [],
    ) {
        $this->checkMissingKeys();
    }


    public function has(string $key): bool
    {
        return Arr::has($this->items, $key);
    }

    /**
     * @param  array<string>|string  $key
     */

    public function get(array|string $key, mixed $default = null): mixed
    {
        if (is_array($key)) {
            return $this->getMany($key);
        }

        return Arr::get($this->items, $key, $default);
    }

    /**
     * @param  array<string>  $keys
     * @return array<string, mixed>
     */

    public function getMany(array $keys): array
    {
        $config = [];

        foreach ($keys as $key => $default) {
            if (is_numeric($key)) {
                [$key, $default] = [$default, null];
            }

            $config[$key] = Arr::get($this->items, $key, $default);
        }

        return $config;
    }

    public function set(string $key, mixed $value = null): void
    {
        Arr::set($this->items, $key, $value);
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->items;
    }


    public function offsetExists(mixed $offset): bool
    {
        return $this->has(strval($offset));
    }


    public function offsetGet(mixed $offset): mixed
    {
        return $this->get(strval($offset));
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set(strval($offset), $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->set(strval($offset), null);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function checkMissingKeys(): bool
    {
        if (empty($this->requiredKeys)) {
            return true;
        }

        $missingKeys = [];

        foreach ($this->requiredKeys as $key) {
            if (! $this->has($key)) {
                $missingKeys[] = $key;
            }
        }

        if (! empty($missingKeys)) {
            throw new InvalidArgumentException(sprintf("\"%s\" cannot be empty.\r\n", implode(',', $missingKeys)));
        }

        return true;
    }
}
