<?php
declare(strict_types = 1);
namespace SaQle\Dao\Field\Attributes;

#[\Attribute(Attribute::TARGET_PROPERTY)]
class JoinField extends SpecialField{
	 /**
	 * Create a new join field instance
	 * @param string $type
	 * @param string $table
	 * @param nullable string $from:     the field name of base table
	 * @param nullable string $to:       the field name of joining table
	 * @param nullable array $fields:    a list of fields to select
	 * @param nullable string $as:       the aliase of the base table
	 * @param nullable string $database: the name of the database
	 */
	 public function __construct(
	 	private string  $type,
	 	private string  $table,
	 	private ?string $from     = null,
	 	private ?string $to       = null,
        private ?array  $fields   = null,
	 	private ?string $as       = null,
	 	private ?string $database = null,
	 ){}

	 public function get_type() : string{
	 	return $this->type;
	 }
	 public function get_table() : string{
	 	return $this->table;
	 }
	 public function get_from() : string|null{
	 	return $this->from;
	 }
	 public function get_to() : string|null{
	 	return $this->to;
	 }
	 public function get_fields() : array|null{
	 	return $this->fields;
	 }
	 public function get_as() : string|null{
	 	return $this->as;
	 }
	 public function get_database() : string|null{
	 	return $this->database;
	 }
}
?>
