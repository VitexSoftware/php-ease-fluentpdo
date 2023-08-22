<?php

/**
 * FluentPDO debugger class
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2021-2023 Vitex@hippy.cz (G)
 */

namespace Ease\SQL;

/**
 * Ease/Fluent PDO Debugger
 */
class Debugger extends \Ease\Sand
{

    /**
     * Ease SQL Debugger
     * 
     * @param \Envms\FluentPDO\Query $fluent
     * @param mixed $caller Description
     */
    function __construct($fluent, $caller)
    {
        $query = $fluent->getQuery();
        $parameters = $fluent->getParameters();
        $this->addStatusMessage($parameters ? vsprintf(str_replace('?', "'%s'", $query), $parameters) : $query, 'debug', $caller);
    }
}
