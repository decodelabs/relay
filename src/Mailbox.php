<?php

/**
 * @package Relay
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Relay;

use DecodeLabs\Exceptional;
use DecodeLabs\Nuance\Dumpable;
use DecodeLabs\Nuance\Entity\NativeObject as NuanceEntity;
use Stringable;

class Mailbox implements
    Dumpable,
    Stringable
{
    public ?string $name = null;

    public string $address {
        set {
            if(str_contains($value, '<')) {
                $parts = explode('<', $value, 2);
                $value = rtrim(trim((string)array_pop($parts)), '>');
                $name = trim((string)array_shift($parts), ' "\'');

                if (empty($name)) {
                    $name = null;
                }

                $this->name = $name;
            }

            $value = strtolower((string)$value);
            $value = str_replace([' at ', ' dot '], ['@', '.'], $value);
            $value = filter_var($value, FILTER_SANITIZE_EMAIL);

            if (
                $value === false ||
                !filter_var($value, FILTER_VALIDATE_EMAIL)
            ) {
                throw Exceptional::InvalidArgument(
                    'Invalid email address: ' . $value
                );
            }

            $this->address = $value;
        }
    }

    public string $domain {
        get {
            $parts = explode('@', $this->address, 2);
            return array_pop($parts);
        }
    }

    /**
     * @return ($mailbox is null ? null : self)
     */
    public static function create(
        string|self|null $mailbox
    ): ?self {
        if (is_string($mailbox)) {
            $mailbox = new self($mailbox);
        }

        return $mailbox;
    }

    public function __construct(
        string $address,
        ?string $name = null
    ) {
        $this->name = $name;
        $this->address = $address;
    }

    public function __toString(): string
    {
        $output = $this->address;

        if ($this->name !== null) {
            $output = '"' . str_replace('"', '\"', $this->name) . '" <' . $output . '>';
        }

        return $output;
    }

    public function toNuanceEntity(): NuanceEntity
    {
        $entity = new NuanceEntity($this);
        $entity->itemName = $this->__toString();
        return $entity;
    }
}
