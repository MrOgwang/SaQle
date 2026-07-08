<?php
namespace SaQle\Log;

interface ReadWriteModes {
	 const START_READ_ONLY = "r";
	 const START_READ_WRITE = "r+";
	 const CLEAR_WRITE_ONLY = "w";
	 const CLEAR_READ_WRITE = "w+";
	 const APPEND_WRITE_ONLY = "a";
	 const APPEND_READ_WRITE = "a+";
	 const INSTANCE_WRITE_ONLY = "x";
	 const INSTANCE_READ_WRITE = "x+";
}