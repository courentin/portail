<?php
/**
 * Notifie lors de la cotisation.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2018, SiMDE-UTC
 * @license GNU GPL-3.0
 */

namespace App\Notifications\User;

use App\Admin\Models\Admin;
use App\Notifications\Notification;
use App\Interfaces\Model\CanBeNotifiable;
use Illuminate\Notifications\Messages\MailMessage;

class UserContributionBde extends Notification
{
    protected $semesters;
    protected $money;
    protected $admin;

    /**
     * Déclare le type de notification.
     *
     * @param array   $semesters
     * @param integer $money
     * @param Admin   $admin
     * @return UserContributionBde
     */
    public function __construct(array $semesters, int $money, Admin $admin=null)
    {
        $this->semesters = collect($semesters);
        $this->money = $money;
        $this->admin = $admin;

        parent::__construct($admin ? 'admin' : 'user', null, $admin->getUser());
    }

    /**
     * Sujet de la notification.
     *
     * @param  CanBeNotifiable $notifiable
     * @return string
     */
    protected function getSubject(CanBeNotifiable $notifiable)
    {
        return 'Cotisation BDE pour l\'année '.$this->getSemestersName();
    }

    /**
     * Retourne le nom de cette année.
     *
     * @return string
     */
    protected function getSemestersName()
    {
        return implode($this->semesters->pluck('name')->toArray(), '-');
    }

    /**
     * Contenu texte de la notification.
     *
     * @param  CanBeNotifiable $notifiable
     * @return string
     */
    protected function getContent(CanBeNotifiable $notifiable)
    {
        $content = 'Félicitations et merci !
Vous avez cotisé '.$this->money.'€ pour l\'année '.$this->getSemestersName().' !';

        if ($this->admin) {
            $content .= PHP_EOL.'Opération réalisée par '.$this->admin->name.'.';
        }

        return $content;
    }
}
