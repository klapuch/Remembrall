<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Dibi;

final class MySqlForgottenPasswords implements ForgottenPasswords {
    private $database;

    public function __construct(Dibi\Connection $database) {
        $this->database = $database;
    }

    public function remind(string $email): RemindedPassword {
        $reminder = bin2hex(random_bytes(50)) . ':' . sha1($email);
        $this->database->query(
            'INSERT INTO forgotten_passwords (subscriber_id, reminder)
			VALUES ((SELECT ID FROM subscribers WHERE email = ?), ?)',
            $email, $reminder
        );
        return new MySqlRemindedPassword($reminder, $this->database);
    }
}