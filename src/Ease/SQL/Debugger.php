<?php

/**
 * FluentPDO debugger class
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2021 Vitex@hippy.cz (G)
 */

namespace Ease\SQL;

class Debugger extends \Ease\Sand {

    /**
     * 
     * @param type $fluent
     */
    function __construct($fluent) {
        $query = $fluent->getQuery();
        $parameters = $fluent->getParameters();
        $this->addStatusMessage($parameters ? vsprintf(str_replace('?', "'%s'", $query), $parameters) : $query, 'debug');
    }

}
