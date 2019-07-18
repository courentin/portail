<?php
/**
 * Indique que le modèle peut créer des notifications.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2018, SiMDE-UTC
 * @license GNU GPL-3.0
 */

namespace App\Interfaces\Model;

interface CanNotify
{
    /**
     * @return string|null
     */
    public function getName(): ?string;
}
