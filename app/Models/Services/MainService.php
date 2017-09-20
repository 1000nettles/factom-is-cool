<?php

namespace App\Models\Services;

use Illuminate\Support\Facades\DB;
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
    protected $apiCallTimeLimit    = 5;

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
        $processModel = Process::where('name', 'factomd')->first();

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
    public function callFactomd($commandId, $params = array())
    {
        try {
            $commandsParamsData = [];
            $command = Command::where(
                'process_id', $this->getFactomdProcessId()
            )
                ->where(is_numeric($commandId) ? 'id' : 'identifier', $commandId)
                ->with('CommandsParams')
                ->first();

            if (!$command) {
                return null;
            }

            $this->checkParamsApplicable($command, $params);

            if (!empty($command->commandsParams) && !empty($params)) {
                foreach ($command->commandsParams as $commandsParam) {
                    $commandsParamsData[$commandsParam->identifier] = $params[$commandsParam->identifier];
                }
            }

            if (env('FACTOM_API_USE_COURTESY', false)) {
                $apiAdapter = new FactomAPIAdapter('https://courtesy-node.factom.com/v2');
            } else {
                $cert = env('FACTOM_API_CERT', null);

                $apiAdapter = new FactomAPIAdapter(
                    $cert ? 'https://localhost:8088/v2' : 'http://localhost:8088/v2',
                    $cert,
                    env('FACTOM_API_USERNAME', null),
                    env('FACTOM_API_PASSWORD', null)
                );
            }

            $result = $apiAdapter->call(
                $command->identifier,
                'post',
                $commandsParamsData
            );

            return $result;
        } catch (\Exception $e) {
            //dd($e);
            // TODO: flash message here
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
        $result = $this->callFactomd('properties');

        if ($result === null) {
            return false;
        }

        return true;
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

        if (!$visitor->wasRecentlyCreated && strtotime($visitor->updated_at) >= strtotime($minDate)) {
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
            throw new \Exception('Command with identifier ' . $command->identifier . ' requires parameters.');
        } else if (!$command->commandsParams->isEmpty() && !empty($params)) {
            foreach ($command->commandsParams as $commandsParam) {
                if (empty($params[$commandsParam->identifier])) {
                    throw new \Exception('Command with identifier ' . $command->identifier . ' requires parameter ' . $commandsParam->identifier);
                }
            }
        }
    }

}