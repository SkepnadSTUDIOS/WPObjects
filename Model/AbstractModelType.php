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

namespace WPObjects\Model;

abstract class AbstractModelType extends AbstractModel implements
    \WPObjects\EventManager\ListenerInterface
{
    /**
     * Factory of current model type
     * 
     * @var \WPObjects\Model\ModelTypeFactory
     */
    protected $ModelTypeFactory = null;
    
    /**
     * Factory of current model type
     * 
     * @var \WPObjects\Factory\AbstractModelFactory
     */
    protected $Factory = null;
    
    /**
     * Qualifiers for object classes, 
     * there as qualifier meta attribute of current class object.
     * 
     * Qualifiers - is UML2 type of association (current object to other object)
     * 
     * @var array
     */
    protected $qualifiers = array();
    
    /**
     * Register special methods for reading global WordPress page context 
     * as object of model type. Methods must return ids of current model type.
     * 
     * @var array of callable functions
     */
    protected $context_methods_reading = array();
    
    /**
     * Cache of initialized models
     * 
     * @var \WPObjects\Model\AbstractTypicalModel in array
     */
    protected $initialized = array();
    
    /**
     * identification attribute of the current model type
     * 
     * @var string
     */
    protected $id_attr_name = 'id';
    
    /**
     * Rewrite default qualifiers attributes names
     * 
     * @var array
     */
    protected $qualifiers_attr_names = array();
    
    /**
     * @var \WPObjects\AjaxController\ModelController
     */
    protected $RESTController = null;
    
    /**
     *
     * @var boolean
     */
    protected $unique = true;
    
    abstract public function getModelClassName();
    
    public function toJSON()
    {
        $data = parent::toJSON();
        $data['qualifiers'] = isset($this->qualifiers) ? $this->qualifiers : array();
        $data['qualifiers_attr_names'] = isset($this->qualifiers_attr_names) ? $this->qualifiers_attr_names : array();
        $data['id_attr_name'] = $this->getIdAttrName();
        
        return $data;
    }
    
    public function attach()
    {
        $Controller = $this->getController();
        if ($Controller) {
            $this->getController()->attach();
        }
        
        return $this;
    }
    
    public function detach()
    {
        $Controller = $this->getController();
        if ($Controller) {
            $this->getController()->detach();
        }
        
        return $this;
    }
    
    /**
     * Initialize Model of current model type.
     * Use in factor method initModel
     * 
     * @param type $data
     * @return type
     */
    public function initModel($data)
    {
        $attr_name = $this->getIdAttrName();
        if (is_object($data) && isset($data->$attr_name)) {
            $id = $data->$attr_name;
        } else if (is_array($data) && isset($data[$attr_name])) {
            $id = $data[$attr_name];
        } else {
            return $this->createModel($data);
        }
        
        if (isset($this->initialized[$id]) && $this->unique) {
            return $this->initialized[$id];
        }
        
        $this->initialized[$id] = $this->createModel($data);
        return $this->initialized[$id];
    }
    
    public function createModel($data)
    {
        $class = $this->getModelClassName();
        $Model = new $class($data, $this);
        return $this->getServiceManager()->inject($Model);
    }
    
    /**
     * Return special methods for reading global WordPress page context.
     * 
     * @param string $model_type_id
     * @return callable|null
     */
    public function getContextMethodReading($model_type_id)
    {
        if (isset($this->context_methods_reading[$model_type_id])) {
            return $this->context_methods_reading[$model_type_id];
        }
        
        return null;
    }
    
    /**
     * Return model type object of context of this context compatible
     * 
     * @param \WP_Post $post
     * @return \WPObjects\Model\AbstractModelType
     */
    public function getContextModelType($post)
    {
        $model_type_id = \get_post_type($post);
        if (!in_array($model_type_id, $this->getContextModelTypes())) {
            return null;
        }
        
        return $this->getModelTypeFactory()->get($model_type_id);
    }
    
    /**
     * Return all model types identities which can be context for current model type
     * 
     * @return array of identifiers
     */
    public function getContextModelTypes()
    {
        return array_merge($this->getQualifiersIds(), $this->getAgregatorsIds(), array($this->getId()));
    }
    
    /**
     * Return all post-types identities which can be context for current model type
     * 
     * @return \WPObjects\PostType\PostType
     */
    public function getContextPostTypes()
    {
        $model_types_ids = $this->getContextModelTypes();
        
        $result = array();
        foreach ($model_types_ids as $id) {
            $ModelType = $this->getModelTypeFactory()->get($id);
            if ($ModelType instanceof \WPObjects\PostType\PostType) {
                $result[] = $ModelType;
            }
        }
        
        return $result;
    }
    
    public function getContextPostTypesAsString()
    {
        $PostTypes = $this->getContextPostTypes();
        $string = '';
        foreach ($PostTypes as $key => $PostType) {
            $string .= '<b>' . $PostType->getName() . '</b>';
            $string .= $key !== count($PostTypes) - 1 ? ', ' : '.';
        }
        
        return $string;
    }
    
    /**
     * Return all own attributes names that are qualifiers for other model types. For 
     * realization association.
     * @return array
     */
    public function getQualifiersAttrsNames()
    {
        $result = array();
        foreach ($this->getQualifiers() as $QualifierModelType) {
            $result[] = $this->getQualifierAttrName($QualifierModelType->getId());
        }
        
        return $result;
    }
    
    /**
     * Create qualifier attribute name, if model type is aggregator of current model type, 
     * returned with out changes. 
     * qualifier - is term of UML2
     * @param string $object_type_id
     * @return string
     */
    public function getQualifierAttrName($object_type_id)
    {
        if (!in_array($object_type_id, $this->getQualifiersIds())) {
            return $object_type_id;
        }
        
        foreach ($this->qualifiers_attr_names as $model_type_id => $attr_name) {
            if ($model_type_id === $object_type_id) {
                return $attr_name;
            }
        }
        
        return parent::getQualifierAttrName($object_type_id);
    }
    
    /**
     * Return object of mode types that are aggregates for current model type
     * @return array of objects \WPObjects\Model\AbstractModelType
     */
    protected function getQualifiers()
    {
        return $this->getModelTypeFactory()->get($this->getQualifiersIds(), array(), false);
    }
    
    /**
     * Return identities of mode types that are aggregates for current model type.
     * @return type
     */
    public function getQualifiersIds()
    {
        return $this->qualifiers;
    }
    
    public function hasQualifier($model_type_id)
    {
        if (in_array($model_type_id, $this->qualifiers)) {
            return true;
        }
        
        return false;
    }
    
    public function getAgregatorsIds()
    {
        return $this->getModelTypeFactory()->getAgregators($this)->getResultIds();
    }
    
    public function getAgregator($agregator_id)
    {
        $Result = $this->getModelTypeFactory()->getAgregator($this, $agregator_id)->getResult();
        return current($Result);
    }
    
    /**
     * Return factory of current model type
     * 
     * @return \WPObjects\Factory\AbstractModelFactory
     * @throws \Exception
     */
    public function getFactory()
    {
        if (!is_null($this->Factory)) {
            return $this->Factory;
        }
        
        if (isset($this->factory_service_name)) {
            return $this->getServiceManager()->get($this->factory_service_name);
        }
        
        throw new \Exception('Undefined model type factory in ' . $this->getId() . '!');
    }
    
    public function setFactory(\WPObjects\Factory\AbstractModelFactory $Factory)
    {
        $this->Factory = $Factory;
        
        return $this;
    }
    
    public function getModelTypeFactory()
    {
        if (is_null($this->ModelTypeFactory)) {
            throw new \Exception('Undefined model type factory in ' . $this->getId());
        }
        
        return $this->ModelTypeFactory;
    }
    
    public function setModelTypeFactory(\WPObjects\Model\ModelTypeFactory $Factory)
    {
        $this->ModelTypeFactory = $Factory;
    }
    
    public function setController(\WPObjects\AjaxController\ModelController $Controller)
    {
        $this->RESTController = $Controller;
        $Controller->setFactory($this->getFactory());
        $Controller->setObjectTypeName($this->getId());
        
        return $this;
    }
    
    /**
     * @return \WPObjects\AjaxController\ModelController
     */
    public function getController()
    {
        return $this->RESTController;
    }
    
    public function getIdAttrName()
    {
        return $this->id_attr_name;
    }
}