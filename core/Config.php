<?php

namespace Core;

/**
 * CONFIGURATIONS
 *
 * PHP version 7.0
 */
class Config
{

    /**
     * Hide-show errors on the screen ( browser console )
     * false by default
     * @var boolean
     */
    const SHOW_ERRORS = true;


    /**
     * Main folder to save
     * @var string
     */
    const BASE_DIR = '../DUMP/';


    /**
     * Language of messages - ru, en
     * ru by default
     * @var string
     */
    const LANG = 'en';


    /**
     * Limit of proxy ip to use
     * when limit is end, script will stop 
     * 20 by default
     * @var integer
     */
    const IP_LIMIT = 20;
}
