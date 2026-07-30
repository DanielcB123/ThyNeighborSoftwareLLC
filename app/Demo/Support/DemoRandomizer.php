<?php

declare(strict_types=1);

namespace App\Demo\Support;

use App\Demo\Enums\DemoDataProfile;

final class DemoRandomizer
{
    private string $seed;

    private int $cursor = 0;

    public function __construct(
        string $datasetVersion,
        string $tenantPublicId,
        DemoDataProfile $profile,
    ) {
        $this->seed = hash('sha256', $datasetVersion.$tenantPublicId.$profile->value);
    }

    public function int(int $min, int $max): int
    {
        if ($max < $min) {
            return $min;
        }

        $segment = substr(hash('sha256', $this->seed.':'.$this->cursor), 0, 15);
        $this->cursor++;
        $value = hexdec($segment);

        return $min + ($value % (($max - $min) + 1));
    }

    public function bool(int $trueOutOfHundred = 50): bool
    {
        return $this->int(1, 100) <= max(0, min(100, $trueOutOfHundred));
    }

    /**
     * @template T
     * @param  list<T>  $values
     * @return T|null
     */
    public function pick(array $values): mixed
    {
        if ($values === []) {
            return null;
        }

        $index = $this->int(0, count($values) - 1);

        return $values[$index];
    }
}
