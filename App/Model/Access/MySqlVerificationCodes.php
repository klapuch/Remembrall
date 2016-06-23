<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Dibi;

final class MySqlVerificationCodes implements VerificationCodes {
    private $database;

    public function __construct(Dibi\Connection $database) {
        $this->database = $database;
    }

    public function generate(string $email): VerificationCode {
        $code = bin2hex(random_bytes(25)) . ':' . sha1($email);
        $this->database->query(
            'INSERT INTO verification_codes (subscriber_id, code)
			VALUES ((SELECT ID FROM subscribers WHERE email = ?), ?)',
            $email, $code
        );
        return new DisposableVerificationCode($code, $this->database);
    }
}