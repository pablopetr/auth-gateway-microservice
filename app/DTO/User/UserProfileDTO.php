<?php

namespace App\DTO\User;

use App\Models\User;

final class UserProfileDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $emailVerifiedAt,
    ) {}

    public static function fromUser(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            emailVerifiedAt: $user->email_verified_at?->toDateTimeString(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->emailVerifiedAt,
        ];
    }
}
