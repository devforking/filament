<?php

namespace Filament\Tables\Actions;

use Filament\Support\Actions\Concerns\CanCustomizeProcess;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DissociateAction extends Action
{
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'dissociate';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-support::actions/dissociate.single.label'));

        $this->modalHeading(fn (): string => __('filament-support::actions/dissociate.single.modal.heading', ['label' => $this->getRecordTitle()]));

        $this->modalButton(__('filament-support::actions/dissociate.single.modal.actions.dissociate.label'));

        $this->successNotificationTitle(__('filament-support::actions/dissociate.single.messages.dissociated'));

        $this->color('danger');

        $this->icon('heroicon-m-x-mark');

        $this->requiresConfirmation();

        $this->action(function (): void {
            $this->process(function (Model $record, Table $table): void {
                /** @var BelongsTo $inverseRelationship */
                $inverseRelationship = $table->getInverseRelationshipFor($record);

                $inverseRelationship->dissociate();
                $record->save();
            });

            $this->success();
        });
    }
}
