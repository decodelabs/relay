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

/**
 * @implements IteratorAggregate<string,Mailbox>
 */
class MailboxList implements
    IteratorAggregate,
    Countable,
    Dumpable
{
    /**
     * @var array<string,Mailbox>
     */
    protected array $mailboxes = [];

    public function __construct(
        string|Mailbox ...$mailboxes
    ) {
        foreach ($mailboxes as $mailbox) {
            $this->add($mailbox);
        }
    }

    public function add(
        string|Mailbox $mailbox
    ): void {
        $mailbox = Mailbox::create($mailbox);
        $this->mailboxes[$mailbox->address] = $mailbox;
    }

    public function get(
        string $address
    ): ?Mailbox {
        return $this->mailboxes[$address] ?? null;
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
        string|Mailbox $address
    ): void {
        if($address instanceof Mailbox) {
            $address = $address->address;
        }

        unset($this->mailboxes[$address]);
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

    public function toNuanceEntity(): NuanceEntity
    {
        $entity = new NuanceEntity($this);
        $entity->values = array_values($this->mailboxes);
        $entity->valueKeys = false;
        return $entity;
    }
}
