<?php
declare(strict_types = 1);

namespace SaQle\Core\Forms\Controls;

use SaQle\Core\Forms\Controls\Base\FormControl;

class SelectControl extends FormControl{
    public array $options = [];
    public bool $multiple = false;
    public mixed $size = null;
    public mixed $value = null;

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
