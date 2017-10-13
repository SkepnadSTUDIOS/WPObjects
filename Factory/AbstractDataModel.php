<?php

/**
 * @encoding     UTF-8
 * @copyright    Copyright (C) 2016 Torbara (http://torbara.com). All rights reserved.
 * @license      Envato Standard License http://themeforest.net/licenses/standard?ref=torbara
 * @author       Vladislav Dolgolenko (vladislavdolgolenko.com)
 * @support      support@torbara.com
 */

namespace WPObjects\Factory;

class AbstractDataModel extends AbstractData implements
    \WPObjects\Model\ModelTypeInterface
{
    protected $ModelType = null;
    
    public function getModelType()
    {
        if (is_null($this->ModelType)) {
            throw new \Exception('Undefiend model type!');
        }
        
        return $this->ModelType;
    }
    
    public function setModelType(\WPObjects\Model\AbstractModelType $ModelType)
    {
        $this->ModelType = $ModelType;
    }
    
    protected function initModel($post)
    {
        $class = $this->getModelType()->getModelClassName();
        $Model = new $class($post, $this->getModelType());
        return $this->getServiceManager()->inject($Model);
    }
    
    
}