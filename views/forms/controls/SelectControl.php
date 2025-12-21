<?php
declare(strict_types = 1);

namespace SaQle\Views\Forms\Controls;

use SaQle\Views\Forms\Controls\Base\FormControl;

class SelectControl extends FormControl{
    protected array $options = [];
    protected bool $multiple = false;
    protected mixed $size = null;
    protected mixed $value = null;

    public function render(): string{
        $options_html = '';

        foreach ($this->options as $val => $label) {
            $selected = ($val == $this->value) ? ' selected' : '';
            $options_html .= '<option value="' . htmlspecialchars((string)$val) . '"' . $selected . '>'
                . htmlspecialchars((string)$label)
                . '</option>';
        }

        return '<select '.$this->render_attributes().'>'.$options_html.'</select>';
    }
}
