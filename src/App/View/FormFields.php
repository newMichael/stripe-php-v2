<?php

declare(strict_types=1);

namespace App\View;

final class FormFields
{
	/**
	 * @param array{name_key: string, value_key: string, name: string, value: string} $csrf
	 */
	public static function csrfInputs(array $csrf): string
	{
		$nameKey = self::escape($csrf['name_key'] ?? '');
		$valueKey = self::escape($csrf['value_key'] ?? '');
		$name = self::escape($csrf['name'] ?? '');
		$value = self::escape($csrf['value'] ?? '');

		return sprintf(
			'<input type="hidden" name="%s" value="%s"><input type="hidden" name="%s" value="%s">',
			$nameKey,
			$name,
			$valueKey,
			$value
		);
	}

	public static function methodInput(string $method): string
	{
		return sprintf(
			'<input type="hidden" name="_METHOD" value="%s">',
			self::escape(strtoupper($method))
		);
	}

	private static function escape(string $value): string
	{
		return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}
}
