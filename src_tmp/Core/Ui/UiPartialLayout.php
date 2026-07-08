<?php

namespace SaQle\Core\Ui;

use SaQle\Http\Request\Request;

interface UiPartialLayout extends UiLayout {
     public function compose(Request $request, UiComponent $slot) : UiComponent;
}