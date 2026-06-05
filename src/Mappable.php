<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

/**
 * Opt-in contract for objects that map themselves, without an explicit {@see Mapper} instance.
 *
 * <p>Combines {@see Serializable} and {@see Deserializable}. Pair it with the {@see MappableBehavior}
 * trait to inherit reflection-based <code>buildFrom</code>, <code>toArray</code>, and <code>toJson</code>.</p>
 */
interface Mappable extends Serializable, Deserializable
{
}
