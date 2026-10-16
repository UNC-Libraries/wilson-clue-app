<?php

namespace Tests\Unit\Validation;

use App\Validation\ClueValidator;
use PHPUnit\Framework\TestCase;

class ClueValidatorTest extends TestCase
{
    private ClueValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new ClueValidator();
    }

    
    public function test_it_returns_true_when_value_contains_all_required_characters(): void
    {
        $this->assertTrue($this->validator->checkDnaSequence('dna', 'ghst', null, null));
    }

    
    public function it_returns_true_when_value_contains_all_required_characters_with_repeats(): void
    {
        $this->assertTrue($this->validator->checkDnaSequence('dna', 'gghhsstt', null, null));
    }

    
    public function test_it_returns_true_when_value_contains_all_required_characters_in_different_order(): void
    {
        $this->assertTrue($this->validator->checkDnaSequence('dna', 'thsg', null, null));
    }

    
    public function test_it_returns_false_when_value_is_missing_g(): void
    {
        $this->assertFalse($this->validator->checkDnaSequence('dna', 'hst', null, null));
    }

    
    public function test_it_returns_false_when_value_is_missing_h(): void
    {
        $this->assertFalse($this->validator->checkDnaSequence('dna', 'gst', null, null));
    }

    
    public function test_it_returns_false_when_value_is_missing_s(): void
    {
        $this->assertFalse($this->validator->checkDnaSequence('dna', 'ght', null, null));
    }

    
    public function test_it_returns_false_when_value_is_missing_t(): void
    {
        $this->assertFalse($this->validator->checkDnaSequence('dna', 'ghs', null, null));
    }

    
    public function test_it_returns_false_when_value_is_empty(): void
    {
        $this->assertFalse($this->validator->checkDnaSequence('dna', '', null, null));
    }

    
    public function test_it_returns_false_when_value_contains_only_one_character(): void
    {
        $this->assertFalse($this->validator->checkDnaSequence('dna', 'g', null, null));
    }

    
    public function test_it_returns_true_when_value_contains_extra_characters_alongside_required_ones(): void
    {
        $this->assertTrue($this->validator->checkDnaSequence('dna', 'ghstabc', null, null));
    }
}

