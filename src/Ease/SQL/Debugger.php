<?php

declare(strict_types=1);

/**
 * This file is part of the EaseFluentPDO package
 *
 * https://github.com/VitexSoftware/php-ease-fluentpdo
 *
 * (c) Vítězslav Dvořák <http://vitexsoftware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ease\SQL;

/**
 * Ease/Fluent PDO Debugger.
 */
class Debugger extends \Ease\Sand
{
    /**
     * Ease SQL Debugger.
     *
     * @param \Envms\FluentPDO\Query $fluent
     * @param mixed                  $caller Description
     */
    public function __construct($fluent, $caller)
    {
        $query = $fluent->getQuery();
        $parameters = $fluent->getParameters();
        $this->addStatusMessage(
            $parameters ? vsprintf(str_replace('?', "'%s'", $query), $parameters) : $query,
            'debug',
            $caller,
        );
    }
}
