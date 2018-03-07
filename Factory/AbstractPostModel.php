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

namespace WPObjects\Factory;

abstract class AbstractPostModel extends AbstractModelFactory implements
    \WPObjects\Factory\TypicalModelFactoryInterface
{
    /**
     * Object ModelType of current factory objects
     * @var \WPObjects\Model\AbstractModelType
     */
    protected $ModelType = null;
    
    /**
     * Main query array for \WP_Query object
     * @var array
     */
    protected $query = array();
    
    /**
     * Meta query array for \WP_Query object.
     * At query preparation linked to $this->query['meta_query']
     * @var array
     */
    protected $meta_query = array();
    
    /**
     * @param $id string || integer || array
     * @return \MSP\Model\AbstractModel || null
     */
    public function get($id = null, $filters = array(), $single = true)
    {
        global $post;
        
        if ($id instanceof \WPObjects\Model\AbstractTypicalModel) {
            return $id;
        }
        
        if ($id instanceof \WP_Post) {
            $id = $id->ID;
        }
        
        $this->query(array_merge(array('id' => $id ? $id : $post->ID), $filters));
        
        if ($single) {
            return current($this->getResult());
        } else {
            return $this->getResult();
        }
    }
    
    /**
     * Return array with compatible elements for autocompele selector in Visual Composer.
     * @return array
     */
    function getForVCAutocompele()
    {
        /* @var $Object WPObjects\Model\AbstractModel */
        
        $result = array();
        foreach ($this->getResult() as $Object) {

            $result[] = array(
                'label' => $Object->post_title,
                'value' => $Object->ID,
            );
        }

        return $result;
    }
    
    /**
     * Return array with values for form select element
     * 
     * @return array
     */
    function getForSelect()
    {
        $result = array();
        
        foreach ($this->getResult() as $Object) {
            $result[$Object->getName()] = $Object->getId();
        }
        
        return $result;
    }
    
    /**
     * Build, send query and set results.
     * @param boolean $result_as_object Query flag. How to create result object. 
     * If true, result must be instance of \WP_Query
     * @return \MSP\Model\AbstractModel
     */
    public function doQuery($filters = array(), $result_as_object = false)
    {
        $this->setFilters($filters);
        $this->result_as_object = $result_as_object;
        
        $this->buildQuery()
             ->buildPagination()
             ->buildOrdering()
             ->buildMetaQuery()
             ->sendQuery();
        
        return $this;
    }
    
    protected function buildQuery()
    {
        $this->query = array();
        $this->meta_query = array('relation' => 'AND');
        $this->query = array(
            'post_type' => $this->getModelType()->getId(),
            'post_status' => 'publish',
            'meta_query' => &$this->meta_query,
            'numberposts' => -1
        );
        
        if (isset($this->filters['numberposts'])) {
            $this->query['numberposts'] = (int)$this->filters['numberposts'];
        }
        
        // Deprecated
        if (isset($this->filters['max_count'])) {
            $this->query['numberposts'] = (int)$this->filters['max_count'];
        }
        
        if (isset($this->filters['post_status'])) {
            $this->query['post_status'] = $this->filters['post_status'];
        }
        
        if (isset($this->filters['s']) && $this->filters['s']) {
            $this->query['s'] = trim($this->filters['s']);
        }
        
        if (isset($this->filters['id']) && $this->filters['id']) {
            $this->query['post__in'] = $this->prepareStringToArray($this->filters['id']);
            
            if (!isset($this->filters['id_not_ordered']) || !$this->filters['id_not_ordered']) {
                $this->query['orderby'] = 'post__in';
            }
        }
        
        if (isset($this->filters['_thumbnail_id']) && $this->filters['_thumbnail_id']) {
            $this->meta_query[] = array(
                'key' => '_thumbnail_id',
                'compare' => 'EXISTS'
            );
        }
        
        return $this;
    }
    
    protected function buildPagination()
    {
        if (isset($this->filters['posts_per_page'])) {
            $this->query['posts_per_page'] = $this->filters['posts_per_page'];
        }
        
        if (isset($this->filters['page']) && $this->filters['page'] > 1) {
            $this->query['offset'] = $this->query['posts_per_page'] * ($this->filters['page'] - 1);
        }
        
        return $this;
    }
    
    protected function buildOrdering()
    {
        if (!isset($this->filters['orderby']) || !$this->filters['orderby']) {
            $this->buildDefaultOrgering();
            return $this;
        }
        
        $orderby = $this->filters['orderby'];
        
        if ($this->getModelType()->validateMetaParam($orderby) === false) {
            return $this;
        }
        
        if (strpos($orderby, '_') === 0 && strpos($orderby, 'date') !== false) {
            $this->query['meta_key'] = $orderby;
            $this->query['orderby'] = 'meta_value';
        } else if (strpos($orderby, '_') == 0) {
            $this->query['meta_key'] = $orderby;
            $this->query['orderby'] = 'meta_value_num';
        } else {
            $this->query['orderby'] = $orderby;
        }

        $this->query['order'] = isset($this->filters['order']) ? $this->filters['order'] : 'DESC';
        
        return $this;
    }
    
    protected function buildDefaultOrgering()
    {
        return;
    }
    
    protected function buildMetaQuery()
    {
        foreach ($this->getModelType()->getQualifiersAttrsNames() as $attr) {
            if (!isset($this->filters[$attr]) || !$this->filters[$attr]) {
                continue;
            }
            
            $this->meta_query[] = array(
                'key' => $attr,
                'value' => $this->prepareStringToArray($this->filters[$attr])
            );
        }
        
        $this->buildSpecialMetaQuery();
        
        return $this;
    }
    
    protected function buildSpecialMetaQuery()
    {
        return;
    }
    
    protected function sendQuery()
    {
        if ($this->result_as_object) {
            $result = new \WP_Query($this->query);
            $this->setTotalCount($result->found_posts);
        } else {
            $result = \get_posts($this->query);
        }
        
        $this->initResults($result);
        
        
        return $this;
    }
    
    protected function initResults($result)
    {
        $models_result = array();
        if ($result instanceof \WP_Query) {
            $posts = $result->posts;
            $result->posts = &$models_result;
        } else {
            $posts = $result;
            $result = &$models_result;
        }
        
        foreach ($posts as $post) {
            $models_result[] = $this->initModel($post);
        }
        
        $this->setResult($result);
        
        return $this;
    }
    
    public function getQuery()
    {
        return $this->query;
    }
    
    public function initModel($post)
    {
        return $this->getModelType()->initModel($post);
    }
    
    /**
    * @return \WPObjects\Model\AbstractModelType
    */
    public function getModelType()
    {
        if (is_null($this->ModelType)) {
            throw new \Exception('Undefiend model type in ' . get_class($this));
        }
        
        return $this->ModelType;
    }
    
    public function setModelType(\WPObjects\Model\AbstractModelType $ModelType)
    {
        $this->ModelType = $ModelType;
        
        return $this;
    }
    
}
