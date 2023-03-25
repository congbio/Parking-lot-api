<?php

namespace App\Repositories\Interfaces;

interface IParKingLotRepository extends IRepository
{
    public function showInfo(int $id):mixed;
    public function showInforDetailOfParking(int $id):mixed;
}
