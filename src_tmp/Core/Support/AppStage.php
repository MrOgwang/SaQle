<?php

namespace SaQle\Core\Support;

enum AppStage: int {
     case INITIALIZING        = 0;

     case REQUEST_START       = 1;
     case REQUEST_RESOLUTION  = 2;
     case REQUEST_BOOTSTRAP   = 3;

     case RESPONSE_RESOLUTION = 4;
     case RESPONSE_BOOTSTRAP  = 5;
     case RESPONSE_SEND       = 6;

     case TERMINATED          = 7;

     /**
     * Get the human-readable stage name.
     */
     public function label(): string {
         return match ($this) {
             self::INITIALIZING        => 'initializing',
             self::REQUEST_START       => 'request_start',
             self::REQUEST_RESOLUTION  => 'request_resolution',
             self::REQUEST_BOOTSTRAP   => 'request_bootstrap',
             self::RESPONSE_RESOLUTION => 'response_resolution',
             self::RESPONSE_BOOTSTRAP  => 'response_bootstrap',
             self::RESPONSE_SEND       => 'response_send',
             self::TERMINATED          => 'terminated',
         };
     }

     /**
     * Get the next lifecycle stage, if any.
     */
     public function next(): ?self {
         return self::tryFrom($this->value + 1);
     }

     /**
     * Get the previous lifecycle stage, if any.
     */
     public function previous(): ?self {
         return self::tryFrom($this->value - 1);
     }

     /**
     * Check whether this stage occurs before another stage.
     */
     public function is_before(self $stage): bool {
         return $this->value < $stage->value;
     }

     /**
     * Check whether this stage occurs after another stage.
     */
     public function is_after(self $stage): bool {
         return $this->value > $stage->value;
     }

     /**
     * Check whether this stage has been reached.
     */
     public function has_reached(self $stage): bool {
         return $this->value >= $stage->value;
     }

     /**
     * Return all lifecycle stages in execution order.
     *
     * @return list<self>
     */
     public static function all(): array {
         return self::cases();
     }

     /**
     * Get a stage from its label.
     */
     public static function from_label(string $label): ?self {
         foreach (self::cases() as $stage) {
             if ($stage->label() === $label) {
                 return $stage;
             }
         }

         return null;
     }
}