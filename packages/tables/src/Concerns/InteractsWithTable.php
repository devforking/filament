<?php

namespace Filament\Tables\Concerns;

use Closure;
use Exception;
use Filament\Forms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Contracts\HasRelationshipTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\Relation;
use function Livewire\invade;

trait InteractsWithTable
{
    use CanBeStriped;
    use CanPaginateRecords;
    use CanPollRecords;
    use CanReorderRecords;
    use CanSearchRecords;
    use CanSelectRecords;
    use CanSortRecords;
    use CanToggleColumns;
    use HasActions;
    use HasBulkActions;
    use HasColumns;
    use HasContent;
    use HasEmptyState;
    use HasFilters;
    use HasHeader;
    use HasRecords;
    use HasRecordAction;
    use HasRecordClasses;
    use HasRecordUrl;
    use Forms\Concerns\InteractsWithForms;

    protected Table $table;

    protected bool $shouldMountInteractsWithTable = false;

    public function bootedInteractsWithTable(): void
    {
        $this->table = Action::configureUsing(
            Closure::fromCallable([$this, 'configureTableAction']),
            fn (): Table => BulkAction::configureUsing(
                Closure::fromCallable([$this, 'configureTableBulkAction']),
                fn (): Table => $this->table($this->makeTable()),
            ),
        );

        $this->cacheForm('toggleTableColumnForm', $this->getTableColumnToggleForm());

        $this->cacheForm('tableFiltersForm', $this->getTableFiltersForm());

        $this->getTableColumnToggleForm()->fill(session()->get(
            $this->getTableColumnToggleFormStateSessionKey(),
            $this->getDefaultTableColumnToggleState()
        ));

        $filtersSessionKey = $this->getTableFiltersSessionKey();

        if ($this->shouldPersistTableFiltersInSession() && session()->has($filtersSessionKey)) {
            $this->tableFilters = array_merge(
                $this->tableFilters ?? [],
                session()->get($filtersSessionKey) ?? [],
            );
        }

        if (! count($this->tableFilters ?? [])) {
            $this->tableFilters = null;
        }

        $this->getTableFiltersForm()->fill($this->tableFilters);

        $searchSessionKey = $this->getTableSearchSessionKey();

        if ($this->shouldPersistTableSearchInSession() && session()->has($searchSessionKey)) {
            $this->tableSearch = session()->get($searchSessionKey) ?? '';
        }

        $columnSearchSessionKey = $this->getTableColumnSearchSessionKey();

        if ($this->shouldPersistTableColumnSearchInSession() && session()->has($columnSearchSessionKey)) {
            $this->tableColumnSearchQueries = session()->get($columnSearchSessionKey) ?? [];
        }

        if (! $this->shouldMountInteractsWithTable) {
            return;
        }

        if ($this->getTable()->isPaginated()) {
            $this->tableRecordsPerPage = $this->getDefaultTableRecordsPerPageSelectOption();
        }

        $this->tableSortColumn ??= $this->getTable()->getDefaultSortColumn();
        $this->tableSortDirection ??= $this->getTable()->getDefaultSortDirection();
    }

    public function mountInteractsWithTable(): void
    {
        $this->shouldMountInteractsWithTable = true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->actions($this->getTableActions())
            ->actionsColumnLabel($this->getTableActionsColumnLabel())
            ->actionsPosition($this->getTableActionsPosition())
            ->bulkActions($this->getTableBulkActions())
            ->columns($this->getTableColumns())
            ->columnToggleFormColumns($this->getTableColumnToggleFormColumns())
            ->columnToggleFormWidth($this->getTableColumnToggleFormWidth())
            ->content($this->getTableContent())
            ->contentFooter($this->getTableContentFooter())
            ->contentGrid($this->getTableContentGrid())
            ->defaultSort($this->getDefaultTableSortColumn(), $this->getDefaultTableSortDirection())
            ->description($this->getTableDescription())
            ->emptyState($this->getTableEmptyState())
            ->emptyStateActions($this->getTableEmptyStateActions())
            ->emptyStateDescription($this->getTableEmptyStateDescription())
            ->emptyStateHeading($this->getTableEmptyStateHeading())
            ->emptyStateIcon($this->getTableEmptyStateIcon())
            ->filters($this->getTableFilters())
            ->filtersFormColumns($this->getTableFiltersFormColumns())
            ->filtersFormWidth($this->getTableFiltersFormWidth())
            ->filtersLayout($this->getTableFiltersLayout())
            ->header($this->getTableHeader())
            ->headerActions($this->getTableHeaderActions())
            ->modelLabel($this->getTableModelLabel())
            ->paginated($this->isTablePaginationEnabled())
            ->paginationPageOptions($this->getTableRecordsPerPageSelectOptions())
            ->pluralModelLabel($this->getTablePluralModelLabel())
            ->poll($this->getTablePollingInterval())
            ->recordAction($this->getTableRecordActionUsing())
            ->recordClasses($this->getTableRecordClassesUsing())
            ->recordTitle(fn (Model $record): ?string => $this->getTableRecordTitle($record))
            ->recordUrl($this->getTableRecordUrlUsing())
            ->reorderable($this->getTableReorderColumn())
            ->striped($this->isTableStriped());
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    protected function makeTable(): Table
    {
        return Table::make($this);
    }

    protected function getTableQueryStringIdentifier(): ?string
    {
        return null;
    }

    protected function getIdentifiedTableQueryStringPropertyNameFor(string $property): string
    {
        if (filled($identifier = $this->getTable()->getQueryStringIdentifier())) {
            return $identifier . ucfirst($property);
        }

        return $property;
    }

    protected function getInteractsWithTableForms(): array
    {
        return $this->getTableForms();
    }

    public function getActiveTableLocale(): ?string
    {
        return null;
    }

    protected function getTableForms(): array
    {
        return [
            'mountedTableActionForm' => $this->getMountedTableActionForm(),
            'mountedTableBulkActionForm' => $this->getMountedTableBulkActionForm(),
        ];
    }

    /**
     * @deprecated Override the `table()` method to configure the table.
     */
    protected function getTableQuery(): Builder | Relation | null
    {
        return null;
    }
}
