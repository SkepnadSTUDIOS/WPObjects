<?php

/**
 * @encoding     UTF-8
 * @copyright    Copyright (C) 2017 Torbara (http://torbara.com). All rights reserved.
 * @license      Envato Standard License http://themeforest.net/licenses/standard?ref=torbara
 * @author       Vladislav Dolgolenko (vladislavdolgolenko.com)
 * @support      support@torbara.com
 */

namespace WPObjects\PostType\MetaBox;

use WPObjects\PostType\MetaBox;

abstract class AbstractMetaBox extends MetaBox
{
    public function enqueues()
    {
        $AM = $this->getAssetsManager();
        $AM->enqueueStyle('form');
    }
}