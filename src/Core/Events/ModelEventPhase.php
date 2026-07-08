<?php
namespace SaQle\Core\Events;

final class ModelEventPhase {
     public const CREATING = 'creating';
     public const CREATED  = 'created';

     public const UPDATING = 'updating';
     public const UPDATED  = 'updated';

     public const DELETING = 'deleting';
     public const DELETED  = 'deleted';

     public const TRUNCATING = 'truncating'; //truncating is bulk deletion
     public const TRUNCATED = 'truncated';

     public const SOFT_DELETING = 'soft_deleting'; //where soft deletion has been set for model
     public const SOFT_DELETED = 'soft_deleted';

     public const READING  = 'reading';
     public const READ     = 'read';
}
