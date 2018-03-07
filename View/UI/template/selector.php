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

/* @var $this \WPObjects\View\UI\Selector */

?>
    
<div class="msp-form-group <?php echo $this->vertical === false ? 'horizont' : ''; ?>">

    <?php if ($this->lable): ?>    
    <label for="<?php echo esc_attr($this->name); ?>">
        <?php echo $this->lable; ?> 
        <?php if (isset($this->add_new_link) && $this->add_new_link) : ?>
            <a href="<?php echo $this->add_new_link; ?>" target="_blank" class="add-new">
                <span>add new</span>
                <i class="dashicons dashicons-plus"></i>
            </a>
        <?php endif; ?>
    </label>
    <?php endif; ?>

    <div class="msp-input-group">
        <select 
            name="<?php echo esc_attr($this->name); ?><?php echo $this->array_result ? '[]' : ''; ?>" 
            class="matabox-selectors <?php echo $this->has_images ? 'with-images' : ''; ?>"
            <?php echo $this->multibple ? 'multiple' : ''; ?>
            >
            
            <?php if (!$this->multibple): ?>
                <option value=""><?php echo esc_html_e('N/A', 'msp'); ?></option>
            <?php endif; ?>
        <?php foreach ($this->options as $option) { ?>
            <option 
                value="<?php echo esc_attr( $option['id'] ); ?>" 
                <?php echo isset($option['img']) ? 'data-img="'. $option['img'] .'"' : ''; ?>
                <?php echo isset($option['font-awesome']) ? 'data-font-awesome="'. $option['font-awesome'] .'"' : ''; ?>
                <?php echo in_array($option['id'], $this->selected) ? 'selected' : ''; ?>>
                <?php echo esc_html($option['name']); ?>
            </option>
        <?php } ?>
        </select>

        <div class="clearfix"></div>
    </div>
    
    <?php if ($this->desctiption) : ?>
    <p class="help-block">
        <?php echo esc_html($this->desctiption); ?>
    </p>
    <?php endif; ?>
    
    <div class="clearfix"></div>
</div>
