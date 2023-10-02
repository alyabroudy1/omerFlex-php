<?php

namespace App\servers;

use App\Entity\Server;

interface MovieServerInterface
{
    public function getServerConfig(): Server;
}