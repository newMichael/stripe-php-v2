<?php

declare(strict_types=1);

namespace App\Mail;

use App\Config\AppConfig;
use DateTimeImmutable;
use League\Plates\Engine;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class TestEmailSender
{
	public function __construct(
		private readonly MailerInterface $mailer,
		private readonly Engine $templates,
		private readonly AppConfig $config
	) {
	}

	public function send(?string $to = null, ?string $subject = null): array
	{
		$resolvedTo = trim((string) ($to ?? $this->config->mailTestTo));
		$resolvedSubject = trim((string) ($subject ?? sprintf('%s test email', $this->config->appName)));
		$sentAt = new DateTimeImmutable();

		$email = (new Email())
			->from(new Address($this->config->mailFrom, $this->config->appName))
			->to($resolvedTo)
			->subject($resolvedSubject)
			->text(sprintf("This is a test email from %s.\nSent at %s.\n", $this->config->appName, $sentAt->format(DATE_ATOM)))
			->html($this->templates->render('emails/test', [
				'appName' => $this->config->appName,
				'to' => $resolvedTo,
				'subject' => $resolvedSubject,
				'sentAt' => $sentAt,
			]));

		$this->mailer->send($email);

		return [
			'to' => $resolvedTo,
			'subject' => $resolvedSubject,
			'sent_at' => $sentAt->format(DATE_ATOM),
			'from' => $this->config->mailFrom,
		];
	}
}
