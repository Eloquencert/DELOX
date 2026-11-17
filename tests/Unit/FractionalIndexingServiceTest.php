<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\FractionalIndexingService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FractionalIndexingServiceTest extends TestCase
{
    public function test_it_generates_the_initial_key_and_appends_in_order(): void
    {
        $indexing = new FractionalIndexingService;
        $first = $indexing->generateKeyBetween(null, null);
        $second = $indexing->generateKeyBetween($first, null);
        $third = $indexing->generateKeyBetween($second, null);

        $this->assertSame('a0', $first);
        $this->assertSame('a1', $second);
        $this->assertSame('a2', $third);
    }

    public function test_it_generates_a_key_before_an_existing_key(): void
    {
        $indexing = new FractionalIndexingService;
        $next = 'a0';
        $previous = $indexing->generateKeyBetween(null, $next);

        $this->assertLessThan($next, $previous);
        $this->assertLessThan($next, $indexing->generateKeyBetween(null, $previous));
    }

    public function test_it_inserts_a_key_between_adjacent_positions(): void
    {
        $indexing = new FractionalIndexingService;
        $middle = $indexing->generateKeyBetween('a0', 'a1');

        $this->assertSame('a0V', $middle);
        $this->assertGreaterThan('a0', $middle);
        $this->assertLessThan('a1', $middle);
    }

    public function test_it_can_repeatedly_narrow_a_gap(): void
    {
        $indexing = new FractionalIndexingService;
        $previous = 'a0';
        $next = 'a1';

        for ($iteration = 0; $iteration < 40; $iteration++) {
            $middle = $indexing->generateKeyBetween($previous, $next);

            $this->assertGreaterThan($previous, $middle);
            $this->assertLessThan($next, $middle);
            $next = $middle;
        }
    }

    public function test_it_rejects_invalid_or_unordered_bounds(): void
    {
        $indexing = new FractionalIndexingService;

        $this->expectException(InvalidArgumentException::class);
        $indexing->generateKeyBetween('a1', 'a0');
    }

    public function test_it_rejects_keys_outside_the_supported_character_range(): void
    {
        $indexing = new FractionalIndexingService;

        $this->expectException(InvalidArgumentException::class);
        $indexing->generateKeyBetween('a 0', null);
    }
}
