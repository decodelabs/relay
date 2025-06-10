<?php

/**
 * @package Relay
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Relay;

use ArrayIterator;
use Countable;
use DecodeLabs\Nuance\Dumpable;
use DecodeLabs\Nuance\Entity\NativeObject as NuanceEntity;
use IteratorAggregate;
use Stringable;

/**
 * @implements IteratorAggregate<string,Mailbox>
 */
class MailboxList implements
    IteratorAggregate,
    Countable,
    Stringable,
    Dumpable
{
    /**
     * @var array<string,Mailbox>
     */
    protected array $mailboxes = [];

    public static function parse(
        ?string $mailboxes
    ): self {
        $list = new self();

        if(empty($mailboxes)) {
            return $list;
        }

        $parts = explode(',', $mailboxes);
        $prefix = null;

        foreach($parts as $part) {
            if(!str_contains($part, '@')) {
                if ($prefix !== null) {
                    $prefix .= ',';
                }

                $prefix .= $part;
                continue;
            }

            if ($prefix !== null) {
                $part = $prefix . ',' . $part;
                $prefix = null;
            }

            $part = trim($part);

            if(!empty($part)) {
                $list->add($part);
            }
        }

        return $list;
    }

    public function __construct(
        string|Mailbox ...$mailboxes
    ) {
        $this->add(...$mailboxes);
    }

    public function add(
        string|Mailbox|self ...$mailboxes
    ): void {
        foreach ($mailboxes as $mailbox) {
            if($mailbox instanceof self) {
                $this->add(...$mailbox->toArray());
                continue;
            }

            $mailbox = Mailbox::create($mailbox);
            $this->mailboxes[$mailbox->address] = $mailbox;
        }
    }

    public function get(
        string $address
    ): ?Mailbox {
        return $this->mailboxes[$address] ?? null;
    }

    public function getFirst(): ?Mailbox
    {
        return $this->mailboxes[array_key_first($this->mailboxes)] ?? null;
    }

    public function has(
        string|Mailbox $address
    ): bool {
        if($address instanceof Mailbox) {
            $address = $address->address;
        }

        return isset($this->mailboxes[$address]);
    }

    public function remove(
        string|Mailbox|self ...$mailboxes
    ): void {
        foreach($mailboxes as $mailbox) {
            if($mailbox instanceof self) {
                $this->remove(...$mailbox->toArray());
                continue;
            }

            if($mailbox instanceof Mailbox) {
                $mailbox = $mailbox->address;
            }

            unset($this->mailboxes[$mailbox]);
        }
    }

    public function isEmpty(): bool
    {
        return empty($this->mailboxes);
    }

    public function clear(): void
    {
        $this->mailboxes = [];
    }

    /**
     * @return array<string,string>
     */
    public function toNameList(): array
    {
        $names = [];

        foreach ($this->mailboxes as $mailbox) {
            if ($mailbox->name) {
                $names[$mailbox->address] = $mailbox->name;
            }
        }
        return $names;
    }

    /**
     * @return ArrayIterator<string,Mailbox>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->mailboxes);
    }

    public function count(): int
    {
        return count($this->mailboxes);
    }

    /**
     * @return array<string,Mailbox>
     */
    public function toArray(): array
    {
        return $this->mailboxes;
    }

    public function __toString(): string
    {
        $output = [];

        foreach ($this->mailboxes as $mailbox) {
            $output[] = (string)$mailbox;
        }

        return implode(', ', $output);
    }

    public function toNuanceEntity(): NuanceEntity
    {
        $entity = new NuanceEntity($this);
        $entity->values = array_values($this->mailboxes);
        $entity->valueKeys = false;
        return $entity;
    }
}
