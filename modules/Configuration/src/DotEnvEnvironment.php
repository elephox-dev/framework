<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Closure;
use DateTimeInterface;
use Elephox\Files\Contract\File;

abstract class DotEnvEnvironment extends AbstractEnvironment
{
	/**
	 * @var array<int, Closure(string|null, bool, File): void>
	 */
	protected array $dotEnvFileChangeListeners = [];

	/**
	 * @var array<string, DateTimeInterface>
	 */
	protected array $dotEnvFileTimestamps = [];

	public function getDotEnvFileName(bool $local, ?string $envName = null): string
	{
		$envFile = '.env';
		if ($envName !== null) {
			$envFile .= '.' . $envName;
		}

		if ($local) {
			$envFile .= '.local';
		}

		return $envFile;
	}

	/**
	 * @param callable(string|null, bool, File): void $callback
	 */
	public function addDotEnvChangeListener(callable $callback): int
	{
		$this->dotEnvFileChangeListeners[] = $callback(...);

		return (int) key($this->dotEnvFileChangeListeners);
	}

	public function removeDotEnvChangeListener(int $id): void
	{
		if ($id < 0) {
			return;
		}

		unset($this->dotEnvFileChangeListeners[$id]);
	}

	public function pollDotEnvFileChanged(?string $envName = null, bool $notifyListeners = true): bool
	{
		$changed = $this->updateDotEnvFileTimestamps($envName, false, $notifyListeners);

		return $this->updateDotEnvFileTimestamps($envName, true, $notifyListeners) || $changed;
	}

	protected function updateDotEnvFileTimestamps(?string $envName, bool $local, bool $notifyListeners): bool
	{
		$envFile = $this->root()->file($this->getDotEnvFileName($local, $envName));

		$oldChangedAt = $this->dotEnvFileTimestamps[$envFile->path()] ?? null;
		if ($envFile->exists()) {
			clearstatcache(true, $envFile->path());
			$modifiedAt = $envFile->modifiedAt();

			$this->dotEnvFileTimestamps[$envFile->path()] = $modifiedAt;

			if ($modifiedAt->getTimestamp() !== $oldChangedAt?->getTimestamp()) {
				// file was modified or this is the first time the timestamps are checked

				if ($notifyListeners) {
					foreach ($this->dotEnvFileChangeListeners as $listener) {
						$listener($envName, $local, $envFile);
					}
				}

				return true;
			}
		} elseif ($oldChangedAt !== null) {
			// file was deleted

			unset($this->dotEnvFileTimestamps[$envFile->path()]);

			if ($notifyListeners) {
				foreach ($this->dotEnvFileChangeListeners as $listener) {
					$listener($envName, $local, $envFile);
				}
			}

			return true;
		}

		return false;
	}
}
