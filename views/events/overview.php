<?php $this->layout('layout', ['title' => 'All Events']); ?>

<?php foreach ($events as $event): ?>
	<article>
		<h2><a href="/events/<?= $this->e($event['event_slug']) ?>"><?= $this->e($event['event_title']) ?></a></h2>
		<p><?= date('F j, Y, g:i a', strtotime($event['event_start'])) ?> - <?= date('F j, Y, g:i a', strtotime($event['event_end'])) ?></p>
	</article>
<?php endforeach; ?>