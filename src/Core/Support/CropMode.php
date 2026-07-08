<?php

namespace SaQle\Core\Support;

enum CropMode: int {
     case NONE   = 0;
     case CENTER = 1;
     case TOP    = 2;
     case BOTTOM = 3;
     case LEFT   = 4;
     case RIGHT  = 5;
}