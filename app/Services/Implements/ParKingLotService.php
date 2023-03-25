<?php

namespace App\Services\Implements;

use App\Repositories\Interfaces\IParKingLotRepository;
use Predis\Response\Status;

class ParKingLotService implements \App\Services\Interfaces\IParKingLotService
{
    public function __construct(
        private readonly IParKingLotRepository $parKingLotRepository,
    )
    {
    }

    public function getAllParkingLot(bool $status): array|null
    {
        return $this->parKingLotRepository->all($status);
    }

    public function getParkingLotById(int $id): array|null
    {
        
        return $this->parKingLotRepository->showInfo($id);
    }

    public function editParKingLot(int $id, array $info): mixed
    {
        return true;
    }
    
}
