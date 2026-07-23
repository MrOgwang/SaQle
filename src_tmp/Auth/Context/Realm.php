<?php

namespace SaQle\Auth\Context;

enum Realm : string {
     case PLATFORM = 'platform';
     case APP      = 'app';
}