<?php

namespace TomHart\Restful;

use BadMethodCallException;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response as SymResponse;
use TomHart\Restful\Concerns\HasLinks;

abstract class AbstractRestfulController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;


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

        foreach (collect($request->input())->except('page')->toArray() as $column => $value) {
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

        $model = $this->iterateThroughChildren($model, $request);

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
     * Return the _links
     * @param Request $request
     * @return ResponseFactory|JsonResponse|RedirectResponse|Response|Redirector
     */
    public function options(Request $request)
    {
        $class = $this->newModelInstance();

        if (!($class instanceof HasLinks)) {
            throw new InvalidArgumentException('OPTIONS only works for models implementing HasLinks');
        }

        return $this->return($request, $class->buildLinks(), 'options');
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

        return app($classFQDN);
    }

    /**
     * Looks for an "extra" param in the route, and if it exists, looks for relationships
     * based on that route.
     * @param Model $model
     * @param Request $request
     * @return LengthAwarePaginator|Collection|Model|mixed
     * @throws BadMethodCallException
     */
    private function iterateThroughChildren(Model $model, Request $request)
    {

        // If there's no route or extra param, just return.
        if (!$request->route() ||
            !($request->route() instanceof Route) ||
            !$request->route()->parameter('extra')) {
            return $model;
        }

        $parts = array_filter(explode('/', (string)$request->route()->parameter('extra')));

        // Loop through the parts.
        foreach ($parts as $part) {
            // Look for an array accessor, "children[5]" for example.
            preg_match('/\[(\d+)]$/', $part, $matches);
            $offset = false;

            // If one was found, save the offset and remove it from $part.
            if (!empty($matches[0])) {
                $part = str_replace(array_shift($matches), '', $part);
                $offset = array_shift($matches);
            }

            $model = $model->$part();

            // If it's a relationship, see if it's paginate-able.
            if (stripos(get_class($model), 'Many') !== false) {
                /** @var BelongsToMany|HasMany|HasOneOrMany|MorphMany|MorphOneOrMany|MorphToMany $model */
                $model = $model->paginate();
            } elseif ($model instanceof Relation) {
                $model = $model->getResults();
            }

            // If there is an offset, get it.
            if ($offset !== false) {
                $model = $model[$offset];
            }
        }

        return $model;
    }

    /**
     * Finds the model instance.
     * @param int $id
     * @param Request|null $request
     * @return Model
     */
    private function findModel(int $id, Request $request = null): Model
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
     * Build and return a response.
     * @param Request $request
     * @param mixed $data
     * @param string $method
     * @return JsonResponse|ResponseFactory|Response|RedirectResponse|Redirector
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
                if (($redirect = $this->redirectToShowRoute($request, $data))) {
                    return $redirect;
                }
        }

        return app(ResponseFactory::class)->json($data, $status);
    }

    /**
     * Redirects to the show route for the model if one exists.
     * @param Request $request
     * @param mixed $data
     * @return RedirectResponse|Redirector|null
     */
    private function redirectToShowRoute(Request $request, $data)
    {
        /** @var Route|null $route */
        $route = $request->route();
        if (!$route) {
            return null;
        }

        $name = $route->getName();
        if (!$name) {
            return null;
        }

        $exploded = explode('.', $name);
        array_pop($exploded);
        $topLevel = array_pop($exploded);

        if (!$topLevel) {
            return null;
        }

        $key = Str::singular(str_replace('-', '_', $topLevel));

        return redirect(route("$topLevel.show", [
            $key => $data->id
        ]));
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
        if (!$header || empty($header)) {
            return;
        }

        $relationships = array_filter(explode(',', $header));

        $class = $class->with($relationships);
    }
}
