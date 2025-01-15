<?php
	 $builder = new DI\ContainerBuilder();
	 $builder->addDefinitions(__DIR__.'/definitions.php');
     $container = $builder->build();

     return $container;
?>