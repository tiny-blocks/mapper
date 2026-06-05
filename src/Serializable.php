<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

/**
 * Renders the implementing type to portable forms, without an explicit {@see Mapper} instance.
 *
 * <p>The contract is parameterless on purpose. Implementers may widen it with an optional configuration
 * argument: value objects accept a {@see Configuration}, and collections accept their own key-preservation
 * option. PHP allows the wider signature, so both still satisfy this contract.</p>
 */
interface Serializable
{
    /**
     * Returns the type as a JSON string.
     *
     * @return string The JSON representation.
     */
    public function toJson(): string;

    /**
     * Returns the type as an array.
     *
     * @return array<int|string, mixed> The array representation, with nested objects and collections resolved.
     */
    public function toArray(): array;
}
