<?php

namespace Bozboz\Jam\Http\Controllers;

use Bozboz\Jam\Repositories\Contracts\EntityRepository;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityController extends Controller
{
    protected $repository;

    public function __construct(EntityRepository $repository)
    {
        $this->repository = $repository;
    }

    public function forId($id, $slug=null)
    {
        $entity = $this->repository->find($id);

        if (!$entity) {
            throw new NotFoundHttpException("No entity for ID '{$id}'");
        }

        if ($entity->paths->where('canonical_id', null)->pluck('path')->first()) {
            return redirect()->to($entity->paths->where('canonical_id', null)->pluck('path')->first());
        }

        return $this->render($entity);
    }

    public function forIdWithPath($path, $id, $slug=null)
    {
        return $this->forId($id, $slug);
    }

    public function forPath($path)
    {
        $entity = $this->repository->getForPath($path);

        if (!$entity) {
            $redirect = $this->repository->get301ForPath($path);

            if ($redirect) {
                return redirect($redirect, 301);
            }

            throw new NotFoundHttpException("No entity for path '{$path}'");
        }

        if ( ! $this->repository->isAuthorised($entity)) {
            throw new AccessDeniedHttpException("Access to entity with path '{$path}' is forbidden.");
        }

        return $this->render($entity);
    }

    protected function render($entity)
    {
        $this->repository->hydrate($entity);
        if (!view()->exists($entity->template->view)) {
            return abort(404);
        }
        return view($entity->template->view)->withEntity($entity);
    }
}
