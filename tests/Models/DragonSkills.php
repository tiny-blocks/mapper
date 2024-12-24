<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Models;

enum DragonSkills: string
{
    case FLY = 'fly';
    case SPELL = 'spell';
    case REGENERATION = 'regeneration';
    case ELEMENTAL_BREATH = 'elemental_breath';
}
