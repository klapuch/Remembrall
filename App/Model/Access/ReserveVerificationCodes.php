<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Dibi;
use Remembrall\Exception;

final class ReserveVerificationCodes implements VerificationCodes {
    private $database;

    public function __construct(Dibi\Connection $database) {
        $this->database = $database;
    }

    public function generate(string $email): VerificationCode {
        $code = $this->database->fetchSingle(
            'SELECT code
			FROM verification_codes
			WHERE user_id = (SELECT ID FROM users WHERE email = ?)
			AND used = 0',
            $email
        );
        if(strlen($code))
            return new DisposableVerificationCode($code, $this->database);
        throw new Exception\ExistenceException(
            'Verification code was already used'
        );
    }
}