<?php

/**
 * @encoding     UTF-8
 * @package      WPObjects
 * @link         https://github.com/VladislavDolgolenko/WPObjects
 * @copyright    Copyright (C) 2018 Vladislav Dolgolenko
 * @license      MIT License
 * @author       Vladislav Dolgolenko <vladislavdolgolenko.com>
 * @support      <help@vladislavdolgolenko.com>
 */

namespace WPObjects\View\UI;

abstract class AbstractUI extends \WPObjects\View\View
{
    protected $name = null;
    protected $lable = null;
    protected $vertical = false;
    
    public function enqueues()
    {
        $AM = $this->getAssetsManager();
        
        $AM->enqueueStyle('form')
           ->enqueueScript('selectors');
        
        return $this;
    }
    
    public function setName($string)
    {
        $this->name = $string;
        
        return $this;
    }
    
    public function setLable($text)
    {
        $this->lable = $text;
        
        return $this;
    }
    
    public function setVertical($bollean)
    {
        $this->vertical = $bollean ? true : false;
        
        return $this;
    }
}