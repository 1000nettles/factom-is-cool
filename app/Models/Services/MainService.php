<?php

namespace App\Models\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Entities\Process;
use App\Models\Entities\Command;
use App\Models\Entities\Visitor;
use FactomAPIAdapter;

class MainService
{

    /**
     * The minimum amount of seconds between when a visitor can call the API
     *
     * @var int
     */
    protected $apiCallTimeLimit = 5;

    /**
     * Amount of time in minutes how often we can check the factomd status
     *
     * @var int
     */
    protected $factomdStatusCheckInterval = 2;

    /**
     * The courtesy node factomd instance URL
     *
     * @var string
     */
    protected $courtesyNodeUrl = 'https://courtesy-node.factom.com/v2';

    /**
     * The local node factomd instance URL - secure
     *
     * @var string
     */
    protected $localNodeSecure = 'https://localhost:8088/v2';

    /**
     * The local node factomd instance URL - insecure
     *
     * @var string
     */
    protected $localNodeInsecure = 'http://localhost:8088/v2';

    /**
     * Get the API call time limit
     *
     * @return int
     */
    public function getApiCallTimeLimit()
    {
        return $this->apiCallTimeLimit;
    }

    /**
     * Get the factomd process ID
     *
     * @return int
     * @throws \Exception
     */
    public function getFactomdProcessId()
    {
        $processModel = Process::where('name', 'factomd')
            ->first();

        if (!$processModel) {
            throw new \Exception('Cannot find factomd process model.');
        }

        return (int) $processModel->id;
    }

    /**
     * Call the Factom API through our factomd process
     *
     * @param $commandId
     * @param array $params
     * @return null|object|string
     */
    public function callFactomd($commandId, $params = [])
    {
        try {
            $commandsParamsData = [];
            $command = Command::where(
                'process_id', $this->getFactomdProcessId()
                )
                ->where(
                    is_numeric($commandId) ? 'id' : 'identifier',
                    $commandId
                )
                ->with('CommandsParams')
                ->first();

            if (!$command) {
                return null;
            }

            $this->checkParamsApplicable($command, $params);

            if (!empty($command->commandsParams) && !empty($params)) {
                foreach ($command->commandsParams as $commandsParam) {
                    $identifier = $commandsParam->identifier;
                    $commandsParamsData[$identifier] = $params[$identifier];
                }
            }

            if (env('FACTOM_API_USE_COURTESY', false)) {
                $apiAdapter = new FactomAPIAdapter(
                    $this->courtesyNodeUrl
                );
            } else {
                $cert = env('FACTOM_API_CERT');
                $certUrl = $cert ?
                    $this->localNodeSecure : $this->localNodeInsecure;

                $apiAdapter = new FactomAPIAdapter(
                    $certUrl,
                    $cert,
                    env('FACTOM_API_USERNAME'),
                    env('FACTOM_API_PASSWORD')
                );
            }

            $result = $apiAdapter->call(
                $command->identifier,
                'post',
                $commandsParamsData
            );

            return $result;
        } catch (\Exception $e) {
            Log::critical($e->getMessage());
        }

        return null;
    }

    /**
     * Get the API status of the factomd process
     *
     * @return bool
     */
    public function factomdStatus()
    {
        return Cache::remember(
            'factomd_status',
            $this->factomdStatusCheckInterval,
            function () {
                $result = $this->callFactomd('properties');

                if ($result === null) {
                    return false;
                }

                return true;
            }
        );
    }

    /**
     * Check if the IP can make an API request
     *
     * @param $ip
     * @return bool
     */
    public function checkIpRestriction($ip)
    {
        $minDate = (new \DateTime('now'))
            ->modify('-' . $this->apiCallTimeLimit . ' seconds')
            ->format('m/d/Y h:i:s a');
        $visitor = Visitor::firstOrCreate(['ip' => $ip]);

        if (
            !$visitor->wasRecentlyCreated
            && strtotime($visitor->updated_at) >= strtotime($minDate)
        ) {
            return false;
        } else if (!$visitor->wasRecentlyCreated) {
            $visitor->touch();
        }

        return true;
    }

    /**
     * Check if all provided params satisfy the expected params with the command
     *
     * @param $command
     * @param $params
     * @throws \Exception
     */
    protected function checkParamsApplicable($command, $params)
    {
        if (!$command->commandsParams->isEmpty() && empty($params)) {
            throw new \Exception(
                'Command with identifier '
                . $command->identifier
                . ' requires parameters.'
            );
        } else if (!$command->commandsParams->isEmpty() && !empty($params)) {
            foreach ($command->commandsParams as $commandsParam) {
                if (empty($params[$commandsParam->identifier])) {
                    throw new \Exception(
                        'Command with identifier '
                        . $command->identifier
                        . ' requires parameter '
                        . $commandsParam->identifier
                    );
                }
            }
        }
    }

}
