<?php

namespace SaQle\Core\Support;

enum Environment : string {
     case DEVELOPMENT = 'development';
     case TESTING = 'testing';
     case STAGING = 'staging';
     case PRODUCTION = 'production';
}