<?php

namespace TomHart\Restful;

use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as SymResponse;

abstract class AbstractRestfulController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    /**
     * The views to render.
     * @var array
     */
    protected $views = [];

    /**
     * What Model class to search for entities.
     * @return string
     */
    abstract protected function getModelClass(): string;


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
     * Return a list of matching models.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $builder = $this->createModelQueryBuilder();

        foreach ($request->input() as $column => $value) {
            $this->filterValue($builder, $column, $value);
        }

        $data = $builder->paginate();

        return $this->return($request, $data, 'index');
    }

    /**
     * Handles creating a model. The C of CRUD
     * @param Request $request
     * @return Factory|JsonResponse|View
     */
    public function store(Request $request)
    {
        $model = $this->newModelInstance();

        foreach ($request->input() as $column => $value) {
            $model->$column = $value;
        }

        $model->save();


        return $this->return($request, $this->findModel($model->id), 'store');
    }

    /**
     * Shows a model. The R of CRUD.
     * @param Request $request
     * @param $id
     * @return Factory|JsonResponse|View
     */
    public function show(Request $request, $id)
    {
        $model = $this->findModel($id);

        return $this->return($request, $model, 'show');
    }

    /**
     * Update a record. The U of CRUD.
     * @param Request $request
     * @param $id
     * @return Factory|JsonResponse|View
     */
    public function update(Request $request, $id)
    {
        $model = $this->findModel($id);

        foreach ($request->input() as $column => $value) {
            $model->$column = $value;
        }

        $model->save();

        return $this->return($request, $model, 'update');
    }

    /**
     * Destroy a model. The D of CRUD.
     * @param Request $request
     * @param $id
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
     * Finds the model instance.
     * @param $id
     * @return Model
     */
    private function findModel($id)
    {
        $classFQDN = $this->getModelClass();
        return $classFQDN::findOrFail($id);
    }

    /**
     * Build and return a response.
     * @param Request $request
     * @param mixed $data
     * @param string $method
     * @return Factory|JsonResponse|View
     */
    private function return(Request $request, $data, string $method)
    {

        $status = SymResponse::HTTP_OK;
        switch ($method) {
            case 'store':
                $status = SymResponse::HTTP_CREATED;
                break;
        }

        if ($request->expectsJson()) {
            return response()->json($data, $status);
        }

        if (isset($this->views[$method]) && view()->exists($this->views[$method])) {
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
                $route = $request->route();
                if ($route) {
                    $name = $route->getName();
                    if ($name) {
                        $exploded = explode('.', $name);
                        array_pop($exploded);
                        $topLevel = array_pop($exploded);

                        return redirect(route("$topLevel.show", [
                            str_replace('-', '_', $topLevel) => $data->id
                        ]));
                    }
                }
                break;
        }

        return response()->json($data, $status);
    }
}
