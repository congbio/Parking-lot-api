<?php

namespace App\Rules;

use App\Models\Block;
use App\Models\ParkingLot;
use Illuminate\Contracts\Validation\Rule;

class UniqueBlockNameInParkingLot implements Rule
{
    protected $parkingLot;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(ParkingLot $parkingLot)
    {
        $this->parkingLot=$parkingLot;
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return ! $this->parkingLot->blocks()->where('nameBlock', $value)->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The Block name has already been taken in this parking lot.';
    }
}
