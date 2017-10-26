<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entities\Command;
use App\Models\Services\MainService;
use Illuminate\Support\Facades\Redirect;

class IndexController extends Controller
{

    /**
     * Render the homepage
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $mainService = new MainService();
        $commandsJson = Command::where(
            'process_id', $mainService->getFactomdProcessId()
            )
            ->with('CommandsParams')
            ->get();

        $siteName = $this->getSiteName();
        $status = $mainService->factomdStatus() ? 'Online' : 'Offline';
        $donationAddr = env('FACTOM_DONATION_ADDR');
        $apiUrl = url('/api');

        /*
         * Push result and previous API search data back if it exists
         */
        $lastResult = session('result');
        if ($lastResult
            && !empty($lastResult['json'])
            && !empty($lastResult['command_id'])
        ) {
            $resultsJson = $lastResult['json'];
            $lastCommand = $lastResult['command_id'];
        } else {
            $resultsJson = null;
            $lastCommand = null;
        }

        return view(
            'index',
            compact(
                'commandsJson',
                'siteName',
                'status',
                'donationAddr',
                'apiUrl',
                'resultsJson',
                'lastCommand'
            )
        );
    }

    /**
     * Hit the running factomd instance with a request
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function api(Request $request)
    {
        $mainService = new MainService();
        $ipCheck = $mainService->checkIpRestriction(request()->ip());

        if (!$ipCheck) {
            $request->session()->flash(
                'error',
                'Please wait '
                    . $mainService->getApiCallTimeLimit()
                    . ' seconds between API calls.'
            );

            return Redirect::route('index');
        }

        $commandId = (int) $request->input('command');

        if (!$commandId) {
            return redirect('/');
        }

        $result = $mainService->callFactomd($commandId, $request->all());

        return Redirect::route('index')
            ->with(
                'result',
                ['json' => json_encode($result), 'command_id' => $commandId]
            );
    }

}
