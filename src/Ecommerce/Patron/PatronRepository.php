<?php

declare(strict_types=1);

namespace FORM\Ecommerce\Patron;

use PDO;

class PatronRepository
{
	public function __construct(private readonly PDO $pdo) {}

	public function findByEmail(string $email): ?Patron
	{
		$stmt = $this->pdo->prepare(
			'SELECT * FROM patrons WHERE patron_email = :email LIMIT 1'
		);
		$stmt->execute(['email' => $email]);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		return $row ? Patron::fromRow($row) : null;
	}

	public function save(Patron $patron): Patron
	{
		$this->pdo->prepare(
			'INSERT INTO patrons (patron_fname, patron_lname, patron_email, patron_status)
			VALUES (:fname, :lname, :email, :status)'
		)->execute([
			'fname'  => $patron->firstName,
			'lname'  => $patron->lastName,
			'email'  => $patron->email,
			'status' => $patron->status,
		]);

		return new Patron(
			patronId:  (int) $this->pdo->lastInsertId(),
			firstName: $patron->firstName,
			lastName:  $patron->lastName,
			email:     $patron->email,
			status:    $patron->status,
		);
	}
}
