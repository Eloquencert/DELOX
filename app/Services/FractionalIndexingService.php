<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

final class FractionalIndexingService
{
    public function generateKeyBetween(?string $prev, ?string $next): string
    {
        $this->validateKey($prev);
        $this->validateKey($next);

        if ($prev !== null && $next !== null && strcmp($prev, $next) >= 0) {
            throw new InvalidArgumentException('The previous key must sort before the next key.');
        }

        if ($prev === null && $next === null) {
            return 'a0';
        }

        if ($prev === null) {
            return $this->keyBefore($next);
        }

        if ($next === null) {
            $lastCharacter = ord($prev[strlen($prev) - 1]);

            if ($lastCharacter < ord('~')) {
                return substr($prev, 0, -1).chr($lastCharacter + 1);
            }

            return $prev.'V';
        }

        $commonLength = 0;
        $shorterLength = min(strlen($prev), strlen($next));

        while ($commonLength < $shorterLength && $prev[$commonLength] === $next[$commonLength]) {
            $commonLength++;
        }

        if ($commonLength === strlen($prev)) {
            return $prev.$this->keyBefore(substr($next, $commonLength));
        }

        $previousCharacter = ord($prev[$commonLength]);
        $nextCharacter = ord($next[$commonLength]);

        if ($nextCharacter - $previousCharacter > 1) {
            return substr($prev, 0, $commonLength)
                .chr(intdiv($previousCharacter + $nextCharacter, 2));
        }

        return $prev.'V';
    }

    private function keyBefore(string $next): string
    {
        if ($next === '') {
            throw new InvalidArgumentException('A key cannot be generated before an empty key.');
        }

        $firstCharacter = ord($next[0]);
        $minimumCharacter = ord('!');

        if ($firstCharacter > $minimumCharacter + 1) {
            return chr(intdiv($firstCharacter + $minimumCharacter, 2));
        }

        if ($firstCharacter === $minimumCharacter + 1) {
            return '!V';
        }

        if (strlen($next) === 1) {
            throw new InvalidArgumentException('There is no key between the supplied bounds.');
        }

        return '!'.$this->keyBefore(substr($next, 1));
    }

    private function validateKey(?string $key): void
    {
        if ($key !== null && ($key === '' || preg_match('/[^\x21-\x7E]/', $key) === 1)) {
            throw new InvalidArgumentException('Keys must contain printable, non-space ASCII characters.');
        }
    }
}
