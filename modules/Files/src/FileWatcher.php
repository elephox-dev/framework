<?php
declare(strict_types=1);

namespace Elephox\Files;

use DateTimeInterface;
use Elephox\Collection\ObjectMap;
use Elephox\Events\Contract\Event;
use Elephox\Events\Contract\EventBus as EventBusContract;
use Elephox\Events\Contract\Subscription;
use Elephox\Events\EventBus;
use Elephox\Files\Contract\File;

class FileWatcher implements Contract\FileWatcher
{
	private EventBusContract $eventBus;

	/**
	 * @var ObjectMap<File, Subscription> $fileSubscriptions
	 */
	private ObjectMap $fileSubscriptions;

	/**
	 * @var ObjectMap<File, DateTimeInterface> $timestampCache
	 */
	private ObjectMap $timestampCache;

	public function __construct()
	{
		$this->eventBus = new EventBus();
		$this->fileSubscriptions = new ObjectMap();
		$this->timestampCache = new ObjectMap();
	}

	public function add(callable $callback, File ...$files): void
	{
		$subscription = $this->eventBus->subscribe(FileChangedEvent::class, static function (Event $e) use ($callback) {
			assert($e instanceof FileChangedEvent);

			return $callback($e);
		});

		foreach ($files as $file) {
			$this->fileSubscriptions->put($file, $subscription);
		}
	}

	public function remove(File $file): void
	{
		$subscription = $this->fileSubscriptions->get($file);

		$this->eventBus->unsubscribe($subscription);
	}

	public function poll(bool $notifyListeners = true): bool
	{
		$changedEvents = [];

		/** @var File $file */
		foreach ($this->fileSubscriptions->keys() as $file) {
			if ($this->checkFileChanged($file)) {
				$changedEvents[] = new FileChangedEvent($file);
			}
		}

		$changed = !empty($changedEvents);
		if ($changed && $notifyListeners) {
			foreach ($changedEvents as $event) {
				$this->eventBus->publish($event);
			}
		}

		return $changed;
	}

	protected function checkFileChanged(File $file): bool
	{
		/** @var null|DateTimeInterface $oldChangedAt */
		$oldChangedAt = null;
		if ($this->timestampCache->has($file)) {
			$oldChangedAt = $this->timestampCache->get($file);
		}

		if ($file->exists()) {
			clearstatcache(true, $file->path());

			$modifiedAt = $file->modifiedAt();

			$this->timestampCache->put($file, $modifiedAt);

			return $modifiedAt->getTimestamp() !== $oldChangedAt?->getTimestamp();
		}

		if ($oldChangedAt !== null) {
			$this->timestampCache->remove($file);

			return true;
		}

		return false;
	}
}
