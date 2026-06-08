<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

/**
 * Per-call serialization options: key preservation, omitted fields, and null omission.
 */
final readonly class Configuration
{
    private function __construct(
        private array $omittedFields,
        private KeyPreservation $keyPreservation,
        private NullPreservation $nullPreservation
    ) {
    }

    /**
     * Creates the default configuration: keys preserved, nothing omitted, null-valued properties kept.
     *
     * @return Configuration The default instance.
     */
    public static function default(): Configuration
    {
        return new Configuration(
            omittedFields: [],
            keyPreservation: KeyPreservation::PRESERVE,
            nullPreservation: NullPreservation::KEEP
        );
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
            keyPreservation: $this->keyPreservation,
            nullPreservation: $this->nullPreservation
        );
    }

    /**
     * Tells whether properties whose value is null are omitted from serialization.
     *
     * @return bool True when null-valued properties are omitted, false otherwise.
     */
    public function omitsNulls(): bool
    {
        return $this->nullPreservation->shouldOmitNulls();
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
     * Returns a copy that omits properties whose value is null from serialization.
     *
     * @return Configuration The new instance.
     */
    public function omittingNulls(): Configuration
    {
        return new Configuration(
            omittedFields: $this->omittedFields,
            keyPreservation: $this->keyPreservation,
            nullPreservation: NullPreservation::OMIT
        );
    }

    /**
     * Returns a copy that discards array keys of iterable content.
     *
     * @return Configuration The new instance.
     */
    public function discardingKeys(): Configuration
    {
        return new Configuration(
            omittedFields: $this->omittedFields,
            keyPreservation: KeyPreservation::DISCARD,
            nullPreservation: $this->nullPreservation
        );
    }
}
