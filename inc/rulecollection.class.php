<?php

class PluginFormcreatorRuleCollection extends RuleCollection
{
    /**
     * The right name for this class
     *
     * @var string
     */
    static $rightname = "config";

    /**
     * Set stop play rules when have the first rule of list match
     *
     * @var boolean
     */
    public $stop_on_first_match=true;

    /**
     * Get name of this type by language of the user connected
     *
     * @return string name of this type
     */
    function getTitle() {
        return ('Pool de règles');
    }
}