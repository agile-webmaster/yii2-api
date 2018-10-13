<?php

/**
 * Class NoAuthWebApi
 * @package api_web\classes
 * @createdBy Basil A Konakov
 * @createdAt 2017-09-04
 * @author YYY
 * @module WEB-API
 * @version 2.0
 */

namespace api_web\classes;

use Yii;
use api_web\modules\integration\classes\sync\AbstractSyncFactory;
use api_web\modules\integration\classes\SyncLog;
use api_web\modules\integration\classes\SyncServiceFactory;
use yii\web\BadRequestHttpException;
use common\models\AllServiceOperation;
use common\models\OuterTask;

class NoAuthWebApi
{
    public function loadDictionary(OuterTask $task)
    {

        # 2.1.1. Trace callback operation with task_id
        SyncLog::trace('Callback operation `task_id` params is ' . $task->id);

        # 2.1.2. Check oper_code
        $oper = AllServiceOperation::findOne($task->oper_code);
        if (!$oper) {
            SyncLog::trace('Operation code ('.$task->oper_code.') is wrong!');
            throw new BadRequestHttpException("wrong_param|" . AbstractSyncFactory::CALLBACK_TASK_IDENTIFIER);
        }
        $allOpers = AbstractSyncFactory::getAllSyncOperations();

        SyncLog::trace('Try to receive XML data...');
        if (array_key_exists($oper->denom, $allOpers) && isset($allOpers[$oper->denom])) {
            $entityName = $allOpers[$oper->denom];
            $entity = new $entityName(SyncServiceFactory::ALL_SERVICE_MAP[$oper->service_id], $oper->service_id);
            /** @var $entity AbstractSyncFactory */
            if (method_exists($entity, 'receiveXmlData')) {
                $res = $entity->receiveXmlData($task, Yii::$app->request->getRawBody());
                SyncLog::trace($res);
                return $res;
            }
        }
        SyncLog::trace('Fail!');
        return 'false';
    }
}