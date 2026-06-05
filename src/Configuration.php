<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

/**
 * Per-call serialization options: key preservation and omitted fields.
 */
final readonly class Configuration
{
    private function __construct(private array $omittedFields, private KeyPreservation $keyPreservation)
    {
    }

    /**
     * Creates the default configuration: keys preserved, nothing omitted.
     *
     * @return Configuration The default instance.
     */
    public static function default(): Configuration
    {
        return new Configuration(omittedFields: [], keyPreservation: KeyPreservation::PRESERVE);
    }

    /**
     * Tells whether the given field is omitted from serialization.
     *
     * @param string $field The candidate property name.
     * @return bool True when the field is omitted, false otherwise.
     */
    public function omits(string $field): bool
    {
        return in_array($field, $this->omittedFields, true);
    }

    /**
     * Returns a copy that omits the given properties from serialization.
     *
     * @param string ...$fields The property names to exclude from the output.
     * @return Configuration The new instance.
     */
    public function omitting(string ...$fields): Configuration
    {
        return new Configuration(
            omittedFields: [...$this->omittedFields, ...$fields],
            keyPreservation: $this->keyPreservation
        );
    }

    /**
     * Returns the given iterable with keys arranged per the configured key preservation.
     *
     * @param array<int|string, mixed> $values The serialized iterable.
     * @return array<int|string, mixed> The values keyed per the configured preservation.
     */
    public function arrangeKeys(array $values): array
    {
        return $this->keyPreservation->shouldPreserveKeys() ? $values : array_values($values);
    }

    /**
     * Returns a copy that discards array keys of iterable content.
     *
     * @return Configuration The new instance.
     */
    public function discardingKeys(): Configuration
    {
        return new Configuration(omittedFields: $this->omittedFields, keyPreservation: KeyPreservation::DISCARD);
    }
}
