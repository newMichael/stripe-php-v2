<?php

declare(strict_types=1);

namespace FORM\Ecommerce\Order;

class OrderAddress
{
	public function __construct(
		public readonly ?int $addressId,
		public readonly int $orderId,
		public readonly OrderAddressType $addressType,
		public readonly ?string $addressFirstName,
		public readonly ?string $addressLastName,
		public readonly ?string $addressEmail,
		public readonly ?string $addressLine1,
		public readonly ?string $addressLine2,
		public readonly ?string $addressCity,
		public readonly ?string $addressState,
		public readonly ?string $addressPostalCode,
		public readonly ?string $addressCountry,
	) {}

	public static function fromRow(array $row): self
	{
		return new self(
			addressId: (int) $row['address_id'],
			orderId: (int) $row['order_id'],
			addressType: OrderAddressType::from($row['address_type']),
			addressFirstName: $row['address_first_name'] ?? null,
			addressLastName: $row['address_last_name'] ?? null,
			addressEmail: $row['address_email'] ?? null,
			addressLine1: $row['address_line1'] ?? null,
			addressLine2: $row['address_line2'] ?? null,
			addressCity: $row['address_city'] ?? null,
			addressState: $row['address_state'] ?? null,
			addressPostalCode: $row['address_postal_code'] ?? null,
			addressCountry: $row['address_country'] ?? null,
		);
	}
}
