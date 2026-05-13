<?php

namespace SaQle\Core\Ui;

use SaQle\Http\Request\Request;

interface UiCompleteLayout extends UiLayout {
      public function compose(Request $request) : UiComponent;
}