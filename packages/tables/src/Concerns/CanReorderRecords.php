<?php

namespace Filament\Tables\Concerns;

use Filament\Tables\Contracts\HasRelationshipTable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait CanReorderRecords
{
    public bool $isTableReordering = false;

    public function reorderTable(array $order): void
    {
        if (! $this->getTable()->isReorderable()) {
            return;
        }

        $orderColumn = $this->getTableReorderColumn();

        if (
            (($relationship = $this->getTable()->getRelationship()) instanceof BelongsToMany) &&
            in_array($orderColumn, $relationship->getPivotColumns())
        ) {
            foreach ($order as $index => $recordKey) {
                $this->getTableRecord($recordKey)->{$relationship->getPivotAccessor()}->update([
                    $orderColumn => $index + 1,
                ]);
            }

            return;
        }

        foreach ($order as $index => $recordKey) {
            $this->getTableRecord($recordKey)->update([
                $orderColumn => $index + 1,
            ]);
        }
    }

    public function toggleTableReordering(): void
    {
        $this->isTableReordering = ! $this->isTableReordering;
    }

    public function isTableReordering(): bool
    {
        return $this->getTable()->isReorderable() && $this->isTableReordering;
    }

    protected function isTablePaginationEnabledWhileReordering(): bool
    {
        return false;
    }

    /**
     * @deprecated Override the `table()` method to configure the table.
     */
    protected function getTableReorderColumn(): ?string
    {
        return null;
    }
}
