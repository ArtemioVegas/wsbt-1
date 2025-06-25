<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\User;
use App\Domain\Event\UserChangedEvent;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Note;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testUserCreation(): void
    {
        $name = new Name('newname123');
        $email = new Email('new@mail.com');
        $note = new Note('new-note');

        $user = new User(1, $name, $email, $note);

        $this->assertSame(1, $user->getId());
        $this->assertSame($name, $user->getName());
        $this->assertSame($email, $user->getEmail());
        $this->assertSame($note, $user->getNotes());
        $this->assertNotNull($user->getCreated());
        $this->assertNull($user->getDeleted());
    }

    public function testChangeNameEmailAndNotes(): void
    {
        $user = new User(
            1,
            new Name('oldname123'),
            new Email('old@mail.com'),
            new Note('old'),
        );

        $newName = new Name('newname123');
        $newEmail = new Email('new@mail.com');
        $newNote = new Note('new-note');

        $user->changeName($newName);
        $user->changeEmail($newEmail);
        $user->changeNotes($newNote);

        $this->assertSame($newName, $user->getName());
        $this->assertSame($newEmail, $user->getEmail());
        $this->assertSame($newNote, $user->getNotes());

        $events = $user->getEvents();
        $this->assertCount(3, $events);

        $this->assertInstanceOf(UserChangedEvent::class, $events[0]);
        $this->assertSame('name', $events[0]->fieldName);
        $this->assertSame('oldname123', $events[0]->oldValue);
        $this->assertSame('newname123', $events[0]->newValue);

        $this->assertInstanceOf(UserChangedEvent::class, $events[1]);
        $this->assertSame('email', $events[1]->fieldName);
        $this->assertSame('old@mail.com', $events[1]->oldValue);
        $this->assertSame('new@mail.com', $events[1]->newValue);

        $this->assertInstanceOf(UserChangedEvent::class, $events[2]);
        $this->assertSame('notes', $events[2]->fieldName);
        $this->assertSame('old', $events[2]->oldValue);
        $this->assertSame('new-note', $events[2]->newValue);
    }

    public function testChangeNotesToNull(): void
    {
        $user = new User(
            1,
            new Name('uname123'),
            new Email('old@mail.com'),
            new Note('old'),
        );

        $user->changeNotes(null);
        $this->assertNull($user->getNotes());

        $events = $user->getEvents();
        $this->assertCount(1, $events);

        $this->assertInstanceOf(UserChangedEvent::class, $events[0]);
        $this->assertSame('notes', $events[0]->fieldName);
        $this->assertSame('old', $events[0]->oldValue);
        $this->assertNull($events[0]->newValue);
    }

    public function testDeleteUser(): void
    {
        $user = new User(
            1,
            new Name('uname123'),
            new Email('old@mail.com'),
        );

        $this->assertNull($user->getDeleted());
        $user->delete();
        $this->assertNotNull($user->getDeleted());

        $events = $user->getEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(UserChangedEvent::class, $events[0]);
        $this->assertSame('deleted', $events[0]->fieldName);
        $this->assertNull($events[0]->oldValue);
        $this->assertNotNull($events[0]->newValue);
    }

    public function testChangeNameDoesNotChangeIfSame(): void
    {
        $name = new Name('user1234');
        $user = new User(1, $name, new Email('test@example.com'));

        $user->changeName(new Name('user1234'));

        $this->assertSame('user1234', $user->getName()->value);
        $this->assertEmpty($user->getEvents());
    }

    public function testChangeEmailDoesNotChangeIfSame(): void
    {
        $email = new Email('same@example.com');
        $user = new User(1, new Name('user1234'), $email);

        $user->changeEmail(new Email('same@example.com'));

        $this->assertSame('same@example.com', $user->getEmail()->value);
        $this->assertEmpty($user->getEvents());
    }

    public function testChangeNotesDoesNotChangeIfSame(): void
    {
        $note = new Note('note');
        $user = new User(1, new Name('user1234'), new Email('test@example.com'), $note);

        $user->changeNotes(new Note('note'));

        $this->assertSame('note', $user->getNotes()?->value);
        $this->assertEmpty($user->getEvents());
    }

    public function testChangeNotesFromNullToNullDoesNothing(): void
    {
        $user = new User(1, new Name('user1234'), new Email('test@example.com'), null);

        $user->changeNotes(null);

        $this->assertNull($user->getNotes());
        $this->assertEmpty($user->getEvents());
    }
}
