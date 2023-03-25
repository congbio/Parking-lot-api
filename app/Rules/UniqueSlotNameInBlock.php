<?php

namespace App\Rules;

use App\Models\Block;
use Illuminate\Contracts\Validation\Rule;

class UniqueSlotNameInBlock implements Rule
{
    protected $block;

    public function __construct(Block $block)
    {
        $this->block = $block;
    }

    public function passes($attribute, $value)
    {
        return ! $this->block->slots()->where('slotName', $value)->exists();
    }

    public function message()
    {
        return 'The slot name has already been taken in this block.';
    }
}
