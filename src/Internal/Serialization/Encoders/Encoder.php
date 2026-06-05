<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Serialization\Encoders;

use TinyBlocks\Mapper\Internal\Metadata\ClassDescriptor;

/**
 * Encodes an instance of the type a class descriptor describes into its portable value.
 */
interface Encoder
{
    /**
     * Encodes the subject into the portable value for the described type.
     *
     * @param object $subject The subject to encode.
     * @param ClassDescriptor $descriptor The descriptor of the subject type.
     * @return mixed The encoded value.
     */
    public function encode(object $subject, ClassDescriptor $descriptor): mixed;

    /**
     * Tells whether this encoder handles the given subject for the described type.
     *
     * @param object $subject The subject to inspect.
     * @param ClassDescriptor $descriptor The descriptor of the subject type.
     * @return bool True when this encoder can encode the subject, false otherwise.
     */
    public function supports(object $subject, ClassDescriptor $descriptor): bool;
}
