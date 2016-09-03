<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

/**
 * Limited interval by the given range
 */
final class LimitedInterval implements Interval {
    const FROM = 0;
    const TO = 1;
	private $origin;
	private $range;

	public function __construct(Interval $origin, array $range) {
		$this->origin = $origin;
		$this->range = $range;
	}

    public function start(): \DateTimeInterface {
        return $this->onAllowedRange(function() {
            return $this->origin->start();
        });
	}

    public function next(): \DateTimeInterface {
        return $this->onAllowedRange(function() {
            return $this->origin->next();
        });
	}

    public function step(): int {
        return $this->onAllowedRange(function() {
            return $this->origin->step();
        });
    }

   /**
    * On allowed range call the event
    * @param \closure $event
    * @return int|DateTimeInterface
    * @throws \RuntimeException
    */ 
    private function onAllowedRange(\closure $event) {
        if($this->underflowed()) {
            throw new \UnderflowException(
                sprintf(
                    'The range limit %s has been underflowed',
                    $this->readableRange()
                )
            );
        }
        elseif($this->overstepped()) {
            throw new \OverflowException(
                sprintf(
                    'The range limit %s has been overstepped',
                    $this->readableRange()
                )
            );
        }
        return $event();
    }

    /**
     * Is the range underflowed?
     * @return bool
     */
    private function underflowed(): bool {
        return $this->origin->step() < $this->limit(self::FROM);
    }

    /**
     * Is the range overstepped?
     * @return bool
     */
    private function overstepped(): bool {
        return $this->origin->step() > $this->limit(self::TO);
    }

    /**
     * Limit by the given position
     * @param int $position
     * @return int
     */
    private function limit(int $position): int {
        return $this->orderedRange()[$position];
    }

    /**
     * Human readable range
     * @return string
     */
    private function readableRange(): string {
        return sprintf(
            'from %d to %d',
            ...$this->orderedRange()
        );
    }


    /**
     * Ascending ordered range
     * FROM will be always minimum
     * TO will be always maximum
     * @return array
     */
    private function orderedRange(): array {
        return [
            self::FROM => min($this->range),
            self::TO =>max($this->range)
        ];
    }
}
