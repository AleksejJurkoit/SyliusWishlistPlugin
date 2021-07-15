<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Entity;

use Ramsey\Uuid\Uuid;

class WishlistToken implements WishlistTokenInterface
{
    /**
     * @var string
     */
    protected $value;

    public function __construct(?string $value = null)
    {
        if ($value === null) {
            $this->value = $this->generate();
        } else {
            $this->setValue($value);
        }
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getValue();
    }

    /**
     * @return string
     */
    private function generate(): string
    {
        return Uuid::uuid4()->toString();
    }
}
