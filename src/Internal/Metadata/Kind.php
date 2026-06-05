<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Metadata;

enum Kind: string
{
    case OBJECT = 'object';
    case DATE_TIME = 'date_time';
    case PURE_ENUM = 'pure_enum';
    case BACKED_ENUM = 'backed_enum';
}
