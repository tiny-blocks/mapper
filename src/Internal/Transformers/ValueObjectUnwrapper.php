<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Transformers;

use TinyBlocks\Mapper\Internal\Detectors\ValueObjectDetector;
use TinyBlocks\Mapper\Internal\Extractors\ValuePropertyExtractor;

final readonly class ValueObjectUnwrapper implements Transformer
{
    public function __construct(
        private ValuePropertyExtractor $extractor,
        private ValueObjectDetector $valueObjectDetector
    ) {
    }

    public function transform(mixed $value): mixed
    {
        $current = $value;

        while (is_object($current) && $this->valueObjectDetector->matches(value: $current)) {
            $current = $this->extractor->extract(object: $current);
        }

        return $current;
    }
}
