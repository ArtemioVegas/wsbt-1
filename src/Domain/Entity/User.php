<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Event\UserChangedEvent;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Note;
use DateTimeImmutable;

class User
{
    private int $id;

    private Name $name;

    private Email $email;

    private ?Note $notes;

    private DateTimeImmutable $created;

    private ?DateTimeImmutable $deleted;

    /** @var array<UserChangedEvent> */
    private array $events = [];

    public function __construct(int $id, Name $name, Email $email, ?Note $note = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->notes = $note;
        $this->created = new DateTimeImmutable();
        $this->deleted = null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getNotes(): ?Note
    {
        return $this->notes;
    }

    public function getCreated(): \DateTimeImmutable
    {
        return $this->created;
    }

    public function getDeleted(): ?\DateTimeImmutable
    {
        return $this->deleted;
    }

    public function changeName(Name $name): void
    {
        if ($this->name->equals($name)) {
            return;
        }
        $this->events[] = new UserChangedEvent($this->id, 'name', $this->name->value, $name->value);
        $this->name = $name;
    }

    public function changeEmail(Email $email): void
    {
        if ($this->email->equals($email)) {
            return;
        }
        $this->events[] = new UserChangedEvent($this->id, 'email', $this->email->value, $email->value);
        $this->email = $email;
    }

    public function changeNotes(?Note $notes): void
    {
        if ($this->notes === null && $notes === null) {
            return;
        }

        if ($this->notes === null || $notes === null) {
            $oldValue = $this->notes?->value;
            $newValue = $notes?->value;

            $this->events[] = new UserChangedEvent($this->id, 'notes', $oldValue, $newValue);
            $this->notes = $notes;

            return;
        }

        if (!$this->notes->equals($notes)) {
            $this->events[] = new UserChangedEvent($this->id, 'notes', $this->notes->value, $notes->value);
            $this->notes = $notes;
        }
    }

    public function delete(): void
    {
        if ($this->deleted !== null) {
            return;
        }
        $this->deleted = new DateTimeImmutable();
        $this->events[] = new UserChangedEvent(
            $this->id,
            'deleted',
            null,
            $this->deleted->format('Y-m-d H:i:s')
        );
    }

    /**
     * @return UserChangedEvent[]
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    public function clearEvents(): void
    {
        $this->events = [];
    }
}
