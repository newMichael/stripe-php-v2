<?php

declare(strict_types=1);

namespace FORM\Ecommerce\Patron;

class Patron
{
	public function __construct(
		public readonly ?int    $patronId,
		public readonly string  $firstName,
		public readonly string  $lastName,
		public readonly string  $email,
		public readonly int     $status = 1,
		public readonly ?string $created = null,
	) {}

	public static function fromRow(array $row): self
	{
		return new self(
			patronId:  (int) $row['patron_id'],
			firstName: $row['patron_fname'],
			lastName:  $row['patron_lname'],
			email:     $row['patron_email'],
			status:    (int) $row['patron_status'],
			created:   $row['patron_created'] ?? null,
		);
	}
}
