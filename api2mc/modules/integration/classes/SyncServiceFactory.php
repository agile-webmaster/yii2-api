<?php

/**
 * Class SyncServiceFactory
 * @package api_web\module\integration
 * @createdBy Basil A Konakov
 * @createdAt 2017-04-20
 * @author YYY
 * @module WEB-API
 * @version 2.0
 */

namespace api_web\modules\integration\classes;

use api_web\modules\integration\classes\sync\AbstractSyncFactory;
use api_web\components\WebApi;
use yii\web\BadRequestHttpException;

class SyncServiceFactory extends WebApi
{

    /** SERVICE RKEEPER name */
    const SERVICE_RKEEPER = 'Rkws';

    /** SERVICE IIKO name */
    const SERVICE_IIKO = 'Iiko';

    /** SERVICE "id - name" mapping */
    const ALL_SERVICE_MAP = [
        1 => self::SERVICE_RKEEPER,
        2 => self::SERVICE_IIKO,
    ];

    public $syncResult = [];

    const TASK_SYNC_GET_LOG = 'get-log';
    const TASK_SYNC_GET_OBJECTS = 'get-rkws-objects';

    const SYNC_TASK_SERVICE_MAPPING = [
        self::TASK_SYNC_GET_OBJECTS => self::SERVICE_RKEEPER,
    ];

    /**
     * Construct method for Class SyncServiceFactory
     * @param mixed $serviceId Service ID
     * @param array $params Transaction params
     * @param string $callbackTaskId Callback task id
     * @throws BadRequestHttpException
     */
    public function __construct($serviceId = 0, array $params = [], string $callbackTaskId = null)
    {

        # 1. Load integration script with application environment params
        parent::__construct();
        SyncLog::trace('Loaded integration script with env and post params');

        # 2. Identify Service ID or CALLLBACK
        if (!$callbackTaskId) {

            # 2.1.1. Identify Service ID
            if (!array_key_exists($serviceId, self::ALL_SERVICE_MAP)) {
                SyncLog::trace('Invalid service_id: "' . $serviceId . '"');
                throw new BadRequestHttpException("empty_param|params");
            } else {
                SyncLog::trace('Identified Service ID: ' . $serviceId);
            }

            # 2.1.2. Use entity class (by factory)
            $entity = $this->factory((int)$serviceId, (string)self::ALL_SERVICE_MAP[$serviceId]);
            SyncLog::trace('Initialized entity class: ' . get_class($entity), self::ALL_SERVICE_MAP[$serviceId]);

            # 2.1.3. Load dictionary data
            /** AbstractSyncFactory $entity */
            $this->syncResult = $entity->loadDictionary($params);

        } elseif ($callbackTaskId == self::TASK_SYNC_GET_LOG) {
            SyncLog::trace('Show log!');
            SyncLog::showLog($params);

        } else {

            # 2.2.1. Find service ID and other params by task_id
            $serviceName = null;
            if (isset(self::SYNC_TASK_SERVICE_MAPPING[$callbackTaskId])) {
                $serviceName = self::SYNC_TASK_SERVICE_MAPPING[$callbackTaskId];
            }
            if (!$serviceName) {
                SyncLog::trace('Invalid service!');
                throw new BadRequestHttpException("Service was not recognized by task_id!");
            }
            $serviceId = array_search($serviceName, self::ALL_SERVICE_MAP);
            if (!$serviceId) {
                SyncLog::trace('Invalid service_id!');
                throw new BadRequestHttpException("empty_param|service_id");
            }

            # 2.2.2. Use entity class (by factory)
            $entity = $this->factory((int)$serviceId, $serviceName);
            SyncLog::trace('Initialized entity class: ' . get_class($entity), self::ALL_SERVICE_MAP[$serviceId]);


            # 2.1.3. Load dictionary data
            /** AbstractSyncFactory $entity */
            $this->syncResult = $entity->getObjects();

        }

    }

    /**
     * Service Class Factory
     * @param int $serviceId Service ID
     * @param string $serviceName Service name
     * @return AbstractSyncFactory
     * @throws BadRequestHttpException
     */
    public function factory(int $serviceId, string $serviceName): AbstractSyncFactory
    {

        $className = __NAMESPACE__ . '\\sync\\Service' . $serviceName;
        if (class_exists($className)) {
            return new $className($serviceName, $serviceId);
        } else {
            SyncLog::trace("The requested service class does not exist!");
            throw new BadRequestHttpException("class_not_exist");
        }
    }

}