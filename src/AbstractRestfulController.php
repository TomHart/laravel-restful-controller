<?php

namespace TomHart\Restful;

use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Route;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as SymResponse;

abstract class AbstractRestfulController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    /**
     * The views to render.
     * @var string[]
     */
    protected $views = [];

    /**
     * What Model class to search for entities.
     * @return string
     */
    abstract protected function getModelClass(): string;

    /**
     * Return a list of matching models.
     * @param Request $request
     * @return JsonResponse|RedirectResponse|ResponseFactory|Response|Redirector
     */
    public function index(Request $request)
    {
        $builder = $this->createModelQueryBuilder();

        foreach ((array)$request->input() as $column => $value) {
            $this->filterValue($builder, $column, $value);
        }

        $data = $builder->paginate();

        return $this->return($request, $data, 'index');
    }

    /**
     * Handles creating a model. The C of CRUD
     * @param Request $request
     * @return JsonResponse|RedirectResponse|ResponseFactory|Response|Redirector
     */
    public function store(Request $request)
    {
        $model = $this->newModelInstance();

        foreach ((array)$request->input() as $column => $value) {
            $model->$column = $value;
        }

        $model->save();


        return $this->return($request, $this->findModel($model->getAttribute('id')), 'store');
    }

    /**
     * Shows a model. The R of CRUD.
     * @param Request $request
     * @param int $id
     * @return JsonResponse|RedirectResponse|ResponseFactory|Response|Redirector
     */
    public function show(Request $request, $id)
    {
        $model = $this->findModel($id, $request);

        return $this->return($request, $model, 'show');
    }

    /**
     * Update a record. The U of CRUD.
     * @param Request $request
     * @param int $id
     * @return JsonResponse|RedirectResponse|ResponseFactory|Response|Redirector
     */
    public function update(Request $request, $id)
    {
        $model = $this->findModel($id);

        foreach ((array)$request->input() as $column => $value) {
            $model->$column = $value;
        }

        $model->save();

        return $this->return($request, $model, 'update');
    }

    /**
     * Destroy a model. The D of CRUD.
     * @param Request $request
     * @param int $id
     * @return ResponseFactory|Response
     * @throws Exception
     */
    public function destroy(Request $request, $id)
    {
        $model = $this->findModel($id);

        $model->delete();

        return response(null, SymResponse::HTTP_NO_CONTENT);
    }

    /**
     * Apply causes to the builder.
     * @param Builder $builder
     * @param string $column
     * @param mixed $value
     */
    private function filterValue(Builder $builder, string $column, $value): void
    {
        $builder->where($column, $value);
    }


    /**
     * Generate a new query builder for the model.
     * @return Builder
     */
    private function createModelQueryBuilder(): Builder
    {
        $class = $this->newModelInstance();

        return $class->newQuery();
    }

    /**
     * Creates a new model instance.
     * @return Model
     */
    private function newModelInstance(): Model
    {
        $classFQDN = $this->getModelClass();

        return new $classFQDN;
    }

    /**
     * Finds the model instance.
     * @param int $id
     * @param Request|null $request
     * @return Model
     */
    private function findModel($id, Request $request = null): Model
    {
        /** @var Model|Builder $class */
        $class = $this->newModelInstance();

        if ($request) {
            $this->preloadRelationships($class, $request);
        }

        $class = $class->findOrFail($id);
        return $class;
    }

    /**
     * Build and return a response.
     * @param Request $request
     * @param mixed $data
     * @param string $method
     * @return JsonResponse|RedirectResponse|ResponseFactory|Response|Redirector
     */
    private function return(Request $request, $data, string $method)
    {

        $status = SymResponse::HTTP_OK;
        switch ($method) {
            case 'store':
                $status = SymResponse::HTTP_CREATED;
                break;
        }

        if ($request->wantsJson()) {
            return app(ResponseFactory::class)->json($data, $status);
        }

        if (isset($this->views[$method]) && app(Factory::class)->exists($this->views[$method])) {
            /** @var View $view */
            $view = view($this->views[$method], [
                'data' => $data
            ]);

            return response($view, $status);
        }

        switch ($method) {
            case 'store':
            case 'update':
                // If it's store/update, and the user isn't asking for JSON, we want to
                // try and redirect them to the related show record page.
                /** @var Route|null $route */
                $route = $request->route();
                if ($route) {
                    $name = $route->getName();
                    if ($name) {
                        $exploded = explode('.', $name);
                        array_pop($exploded);
                        $topLevel = array_pop($exploded);

                        if ($topLevel) {
                            return redirect(route("$topLevel.show", [
                                str_replace('-', '_', $topLevel) => $data->id
                            ]));
                        }
                    }
                }
                break;
        }

        return app(ResponseFactory::class)->json($data, $status);
    }

    /**
     * Preload any relationships required.
     * @param Model $class
     * @param Request $request
     * @return void
     */
    private function preloadRelationships(Model &$class, Request $request): void
    {
        $header = $request->headers->get('X-Load-Relationship');
        if (!$header) {
            return;
        }

        $relationships = explode(',', $header);
        if (!$relationships) {
            return;
        }

        $class = $class->with($relationships);
    }
}
