<?php

namespace Bozboz\Jam\Http\Controllers\Admin;

use Carbon\Carbon;
use Bozboz\Jam\Entities\Entity;
use Illuminate\Support\MessageBag;
use Bozboz\Jam\Entities\EntityPath;
use Illuminate\Support\Facades\App;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Redirect;
use Bozboz\Jam\Entities\EntityPathDecorator;
use Bozboz\Admin\Exceptions\ValidationException;
use Bozboz\Admin\Reports\Actions\Presenters\Link;
use Bozboz\Admin\Reports\Actions\Permissions\IsValid;
use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Admin\Permissions\RestrictAllPermissionsTrait;
use Illuminate\Routing\Exceptions\UrlGenerationException;

class EntityPathController extends ModelAdminController
{
    protected $useActions = true;

    public function __construct(EntityPathDecorator $decorator)
    {
        parent::__construct($decorator);
    }

    public function indexForEntity($entityId)
    {
        request()->merge(['entity' => $entityId]);
        return parent::index();
    }

    public function createForEntity($entityId)
    {
        $instance = $this->decorator->newModelInstance();

        if ( ! $this->canCreate($instance)) App::abort(403);

        $instance->entity()->associate($entityId);
        $instance->deleted_at = Carbon::now();

        return $this->renderFormFor($instance, $this->createView, 'POST', 'store');
    }

    protected function save($instance, $input)
    {
        $instance->fill($input);
        $entity = Entity::find($instance->entity_id);
        try{
            EntityPath::onlyTrashed()->where('entity_id', '<>', $entity->id)->wherePath($input['path'])->forceDelete();
            $path = $entity->paths()->onlyTrashed()->firstOrNew(['path' => $input['path'], 'canonical_id' => $entity->paths()->first()->id]);
            $path->deleted_at = $input['deleted_at'];
            $path->save();
        } catch (QueryException $e) {
            $message = 'There is already a page with the url ' . url(str_replace_array('\?', $e->getBindings(), '?'));
            if (App::runningInConsole()) {
                throw new UrlGenerationException($message);
            } else {
                throw new ValidationException(new MessageBag([
                    'path' => $message
                ]));
            }
        }
    }

    /**
     * Return an array of actions the report can perform
     *
     * @return array
     */
    protected function getReportActions()
    {
        return [
            $this->actions->create(
                [$this->getActionName('createForEntity'), request()->input('entity')],
                [$this, 'canCreate'],
                'New ' . $this->decorator->getHeading(),
                ['class' => 'btn-success pull-right space-left']
            ),
            $this->actions->custom(
                new Link(
                    [app(EntityController::class)->getActionName('show'), Entity::find(request()->input('entity'))->template->type_alias],
                    'Back to listing',
                    'fa fa-list-alt',
                    [
                        'class' => 'btn-default pull-right space-left',
                    ]
                ),
                new IsValid([$this, 'canView'])
            ),
        ];
    }

    protected function getListingUrl($instance)
    {
        return action($this->getActionName('indexForEntity'), $instance->entity->id);
    }

    /**
     * The generic response after a successful store/update action.
     */
    protected function getSuccessResponse($instance)
    {
        return Redirect::action($this->getActionName('indexForEntity'), $instance->entity->id);
    }

    protected function createPermissions($stack, $instance)
    {
        $stack->add('create_entity_type', $instance && $instance->exists ? $instance->entity->template->type_alias : null);
    }

    protected function editPermissions($stack, $instance)
    {
        $stack->add('edit_entity_type', $instance && $instance->exists ? $instance->entity->template->type_alias : null);
    }

    protected function deletePermissions($stack, $instance)
    {
        $stack->add('delete_entity_type', $instance && $instance->exists ? $instance->entity->template->type_alias : null);
    }

    protected function viewPermissions($stack)
    {
        $stack->add('view_entity_type');
    }
}
