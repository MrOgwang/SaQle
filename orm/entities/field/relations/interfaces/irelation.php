<?php
namespace SaQle\Orm\Entities\Field\Relations\Interfaces;

interface IRelation{
	 public ?string $pmodel     { set; get; }
	 public protected(set) string $fmodel     { set; get; }
	 public protected(set) ?string $field      { set; get; }
	 public protected(set) ?string $pk         { set; get; }
	 public protected(set) ?string $fk         { set; get; }
	 public protected(set) bool   $navigation { set; get; }
	 public protected(set) bool   $multiple   { set; get; }
	 public protected(set) bool   $eager      { set; get; }
}
