<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use TinyBlocks\Mapper\ObjectMappability;
use TinyBlocks\Mapper\ObjectMapper;

final readonly class Member implements ObjectMapper
{
    use ObjectMappability;

    public function __construct(
        public MemberId $id,
        public string $role,
        public UserId $userId,
        public bool $isOwner,
        public OrganizationId $organizationId
    ) {
    }
}
