<?php

namespace Freesgen\Atmosphere\Domains\Admin\Http\Controllers;

use Inertia\Inertia;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Insane\Journal\Models\Core\Tax;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Domains\Admin\Data\MailConfigData;
use App\Actions\Journal\CreateTeamSettings;
use Freesgen\Atmosphere\Domains\Admin\Services\EnvironmentService;

class AdminSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $tabName = "business",  EnvironmentService $environmentService)
    {
        return inertia('Settings/Index', [
            "taxesDefinition" => Tax::where('team_id', $request->user()->current_team_id)->get(),
            "tabName" =>  $tabName,
            "settingData" => $this->getBySection($request->user()->current_team_id, $tabName, $environmentService)
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function section(Request $request, $name = "business", EnvironmentService $environmentService)
    {
        $businessData = [];
        $teamId = $request->user()->current_team_id;
        if ($name !== 'business') {
            $businessData = Setting::getByTeam($teamId);
        }

        $taxes = Tax::where('team_id', $teamId)->get();

        return inertia("Settings/".ucfirst($name), [
            "taxes" => $taxes,
            "settingData" => $this->getBySection($teamId, $name, $environmentService),
            "businessData" => $businessData
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Response $response)
    {
        $settings = $request->post();
        $entryData = [
            'user_id' =>  $request->user()->id,
            'team_id' => $request->user()->current_team_id
        ];

        foreach ($settings as $settingName => $setting) {
          //  if (empty($setting) && $setting !== false) continue;
            $setting = array_merge($entryData, [
                "value" => $setting,
                "name" => $settingName
            ]);
            $resource = Setting::where([
                'user_id' =>  $request->user()->id,
                'team_id' => $request->user()->current_team_id,
                'name' => $settingName
            ])->limit(1)->get();

            if (count($resource)) {
                $resource[0]->update($setting);
            } else {
                $resource = Setting::create($setting);
            }
        }

        $res = Setting::getFormatted([
            'user_id' =>  $request->user()->id,
            'team_id' => $request->user()->current_team_id
        ]);

        return $response->setContent($res);
    }

    public function setup(Request $request) {
        (new CreateTeamSettings)->create($request->user()->current_team_id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, string $id, EnvironmentService $environmentService)
    {
        $businessData = [];
        $teamId = $request->user()->current_team_id;
        if ($id !== 'business') {
            $businessData = Setting::getByTeam($teamId);
        }

        $taxes = Tax::where('team_id', $teamId)->get();
        return Inertia::render("Settings/".ucfirst($id), [
            "taxes" => $taxes,
            "settingData" => $this->getBySection($teamId, $id, $environmentService),
            "businessData" => $businessData
        ]);
    }


    /**
     *
     * @param MailEnvironmentRequest $request
     * @return JsonResponse
     */
    public function storeMailConfig(EnvironmentService $environmentService, MailConfigData $data)
    {
      if (! Gate::allows('superadmin')) {
        abort(403);
      }
      $environmentService->saveMailVariables($data);
    }

    public function testEmailConfig(Request $request)
    {
        $this->authorize('manage email config');

        $this->validate($request, [
            'to' => 'required|email',
            'subject' => 'required',
            'message' => 'required',
        ]);

        // Mail::to($request->to)->send(new TestMail($request->subject, $request->message));

        return response()->json([
            'success' => true,
        ]);
    }




    public function getBySection($teamId, string $actionName,  EnvironmentService $environmentService) {
      switch ($actionName) {
        case "email": {
          return $environmentService->getMailEnvironment();
        }
      }
    }

}
