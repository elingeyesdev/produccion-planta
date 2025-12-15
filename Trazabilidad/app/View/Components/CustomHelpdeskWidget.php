<?php

namespace App\View\Components;

use Lukehowland\HelpdeskWidget\View\Components\HelpdeskWidget;

class CustomHelpdeskWidget extends HelpdeskWidget
{
    /**
     * Obtener el primer nombre del usuario desde el modelo Operator
     */
    protected function getUserFirstName($user): string
    {
        return $user->nombre ?? '';
    }
    
    /**
     * Obtener el apellido del usuario desde el modelo Operator
     */
    protected function getUserLastName($user): string
    {
        return $user->apellido ?? '';
    }
}
