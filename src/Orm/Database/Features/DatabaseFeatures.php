<?php
declare(strict_types=1);

namespace SaQle\Orm\Database\Features;

final class DatabaseFeatures {
    public const RETURNING               = 'returning';
    public const UPSERT                  = 'upsert';
    public const NO_OP_UPDATE            = 'no_op_update';
    public const MULTI_ROW_INSERT        = 'multi_row_insert';
    public const TRANSACTIONS            = 'transactions';
    public const SAVEPOINTS              = 'savepoints';
    public const WINDOW_FUNCTIONS        = 'window_functions';
    public const COMMON_TABLE_EXPRESSIONS= 'cte';
    public const JSON_TYPE               = 'json_type';
    public const GENERATED_COLUMNS       = 'generated_columns';
    public const CHECK_CONSTRAINTS       = 'check_constraints';
    public const PARTIAL_INDEXES         = 'partial_indexes';
    public const FULL_TEXT_SEARCH        = 'full_text_search';
    public const DEFERRABLE_CONSTRAINTS  = 'deferrable_constraints';
    public const LOCKING_READS           = 'locking_reads';
}
