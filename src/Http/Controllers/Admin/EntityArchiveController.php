<?php

namespace Bozboz\Jam\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Bozboz\Admin\Reports\PaginatedReport;
use Bozboz\Jam\Entities\Events\EntitySaved;
use Illuminate\Contracts\Events\Dispatcher;
use Bozboz\Jam\Entities\EntityArchiveDecorator;
use Bozboz\Admin\Reports\Actions\Presenters\Form;
use Bozboz\Admin\Reports\Actions\Presenters\Link;
use Bozboz\Admin\Reports\Actions\Permissions\IsValid;
use Bozboz\Jam\Http\Controllers\Admin\EntityController;
use Bozboz\Jam\Repositories\Contracts\EntityRepository;

class EntityArchiveController extends EntityController
{
    public function __construct(EntityArchiveDecorator $decorator, EntityRepository $repo)
    {
        parent::__construct($decorator, $repo);
    }

    /**
     * Get an instance of a report to display the model listing
     *
     * @return Bozboz\Admin\Reports\NestedReport
     */
    protected function getListingReport()
    {
        return new PaginatedReport($this->decorator, Input::get('per-page'));
    }

    public function show($type)
    {
        $this->type = app('EntityMapper')->get($type);
        $this->decorator = app(EntityArchiveDecorator::class);
        $this->decorator->setType($this->type);

        if (!$this->type) {
            throw new NotFoundHttpException;
        }

        if ( ! $this->canShow($type)) App::abort(403);

        $report = $this->getListingReport();

        $report->injectValues(Input::all());

        $report->setReportActions($this->getReportActions());
        $report->setRowActions($this->getRowActions());

        return $report->render();
    }

    public function getReportActions()
    {
        return [
            $this->actions->custom(
                new Link(
                    ['\\'.EntityController::class.'@show', $this->type->alias],
                    'Back to '.$this->type->name, 'fa fa-list-alt', ['class' => 'btn-default pull-right']
                ),
                new IsValid([$this, 'canView'])
            ),
        ];
    }

    protected function getRowActions()
    {
        return [
            // $this->actions->custom(
            //     new Link($this->getEditAction(), 'View', 'fa fa-eye', ['class' => 'btn-info']),
            //     new IsValid([$this, 'canEdit'])
            // ),
            $this->actions->custom(
                new Form($this->getActionName('restore'), 'Restore', 'fa fa-undo', [
                    'class' => 'btn-primary btn-sm',
                ]),
                new IsValid([$this, 'canView'])
            ),
            $this->actions->custom(
                new Form($this->getActionName('destroy'), 'Delete Forever', 'fa fa-trash', [
                    'class' => 'btn-danger btn-sm',
                    'data-warn' => 'Are you sure you want to delete? This cannot be undone.'
                ], [
                    'method' => 'DELETE'
                ]),
                new IsValid([$this, 'canDestroy'])
            ),
        ];
    }

    public function restore(Dispatcher $events, $id)
    {
        DB::beginTransaction();

        $instance = $this->decorator->findInstance($id);

        if ( ! $this->canView($instance)) App::abort(403);

        $instance->children()->withTrashed()->where('deleted_at', $instance->deleted_at)->restore();

        $instance->restore();

        $events->fire(new EntitySaved($instance));

        DB::commit();

        return Redirect::back()->with('model.updated', sprintf(
            'Successfully restored "%s"',
            $this->decorator->getLabel($instance)
        ));
    }

    public function destroy($id)
    {
        $instance = $this->decorator->findInstance($id);

        if ( ! $this->canDestroy($instance)) App::abort(403);

        $instance->currentRevision()->dissociate();
        $instance->save();
        $instance->forceDelete();

        return Redirect::back()->with('model.deleted', sprintf(
            'Successfully deleted "%s"',
            $this->decorator->getLabel($instance)
        ));
    }

    public function deletePermissions($stack, $instance)
    {
        $stack->add('delete_entity_forever', $instance);
    }

    public function viewPermissions($stack)
    {
        $stack->add('view_entity_archive');
    }
}
