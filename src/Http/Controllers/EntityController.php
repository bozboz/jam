<?php

namespace Bozboz\Jam\Http\Controllers;

use Bozboz\Jam\Repositories\Contracts\EntityRepository;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityController extends Controller
{
    private $repository;

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
                return redirect($redirect);
            }

            throw new NotFoundHttpException("No entity for path '{$path}'");
        }

        return $this->render($entity);
    }

    protected function render($entity)
    {
        if ( ! $this->repository->isAuthorised($entity)) {
            throw new AccessDeniedHttpException("Access to entity with path '{$path}' is forbidden.");
        }

        $this->repository->hydrate($entity);

        return view($entity->template->view)->withEntity($entity);
    }
}