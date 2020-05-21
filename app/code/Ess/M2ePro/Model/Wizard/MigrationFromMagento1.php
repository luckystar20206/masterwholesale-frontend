<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Wizard;

use Ess\M2ePro\Model\Wizard;

/**
 * Class MigrationFromMagento1
 * @package Ess\M2ePro\Model\Wizard
 */
class MigrationFromMagento1 extends Wizard
{
    const NICK = 'migrationFromMagento1';

    protected $steps = [
        'synchronization',
        'congratulation'
    ];

    /**
     * @return string
     */
    public function getNick()
    {
        return self::NICK;
    }
}
