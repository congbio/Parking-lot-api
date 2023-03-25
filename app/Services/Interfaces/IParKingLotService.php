<?php

namespace App\Services\Interfaces;

interface IParKingLotService
{

    public function getAllParkingLot(bool $status):array|null;
    public function getParkingLotById(int $id):array|null;
    public function editParKingLot(int$id,array $info):mixed;

}
