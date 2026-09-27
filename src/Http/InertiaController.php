<?php

namespace Freesgen\Atmosphere\Http;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class InertiaController extends BaseController {
    use Querify;
    protected $model;
    protected $templates;
    protected $searchable = ["id"];
    protected $validationRules = [];
    protected $sorts = [];
    protected $includes = [];
    protected $appends = [];
    protected $filters = [];
    protected $responseType = "inertia";
    protected $resourceName;

    protected function index(Request $request) {
        $resourceName = $this->resourceName ?? $this->model->getTable();
        $resources = $this->parser($this->getModelQuery($request));

        return Inertia::render($this->templates['index'],
        array_merge([
            $resourceName => $this->parser($this->getModelQuery($request)),
            "serverSearchOptions" => $this->getServerParams()
        ], $this->getIndexProps($request, $resources)));
    }

    public function create(Request $request) {
        return Inertia::render($this->templates['create'], []);
    }

    public function edit(Request $request, int $id) {
        return Inertia::render($this->templates['edit'], $this->getEditProps($request, $id));
    }

    public function store(Request $request, Response $response) {
        $postData = $request->post();
        $postData['user_id'] = $request->user()->id;
        $postData['team_id'] = $request->user()->current_team_id;
        $this->validate($request, $this->getValidationRules($postData));
        $resource = $this->model::create($postData);
        $this->afterSave($postData, $resource);
        if ($this->responseType == 'inertia') {
            return redirect()->back();
        } else {
            return $response->setContent($resource);
        }
    }

    public function update(Request $request, int $id) {
        $resource = $this->findTeamResource($request, $id);
        $postData = Arr::except($request->post(), ['team_id', 'user_id']);
        $resource->update($postData);
        $this->afterSave($postData, $resource);

        if ($this->responseType == 'inertia') {
            return Redirect::back();
        } else {
            return $resource;
        }
    }

    public function destroy(Request $request, int $id) {
        $resource = $this->findTeamResource($request, $id);
        if ($this->validateDelete($request, $resource)) {
            $resource->delete();
            return Redirect::back();
        } else {
            return Redirect::back()->withErrors(['error' => 'You cannot delete this resource']);
        }
    }

    /**
     * Resolves a record by id within the current team, 404 for anything else.
     */
    protected function findTeamResource(Request $request, int $id) {
        return $this->model::query()
            ->when($this->authorizedTeam, fn ($query) => $query->where('team_id', $request->user()->current_team_id))
            ->findOrFail($id);
    }

    protected function getIndexProps(Request $request, Collection|ResourceCollection $resources): array {
        return [];
    }

    protected function parser($results) {
        return $results;
    }

    protected function getEditProps(Request $request, $id) {
        $resources = $this->getModelQuery($request, $id);
        abort_if($resources->isEmpty(), 404);

        return [
            $this->model->getTable() => $resources[0]
        ];
    }

    protected function afterSave($postData, $resource): void {

    }

    protected function getPostData(Request $request) {
        $postData = $request->post();
        $postData['user_id'] = $request->user()->id;
        $postData['team_id'] = $request->user()->current_team_id;

        return $postData;
    }

    protected function validateDelete(Request $request, $resource) {
        return true;
    }

    protected function getValidationRules($postData) {
        return $this->validationRules;
    }

    protected function getFilterDates($filters = [], string $timeZone = null, $subCount=0) {
        $zone = $timeZone ?? config("app.timezone");
        $dates = isset($filters['date']) ? explode("~", $filters['date']) : [
            Carbon::now()->setTimezone($zone)->subMonths($subCount)->startOfMonth()->format('Y-m-d'),
            Carbon::now()->setTimezone($zone)->endOfMonth()->format('Y-m-d')
        ];
        return $dates;
    }
}
