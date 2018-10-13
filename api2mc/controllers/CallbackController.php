<?php

/**
 * Class CallbackController
 * @package api\modules\v1\modules\web\controllers
 * @createdBy Basil A Konakov
 * @createdAt 2016-10-04
 * @author YYY
 * @module WEB-API
 * @version 2.0
 */

namespace api_web\controllers;

use api_web\modules\integration\classes\sync\AbstractSyncFactory;
use api_web\modules\integration\classes\SyncLog;
use common\models\OuterTask;
use \Yii;
use api_web\components\WebApiNoAuthController;
use yii\web\BadRequestHttpException;

/**
 * Class CallbackController
 * @package api_web\controllers
 */
class CallbackController extends WebApiNoAuthController
{

    /**
     * @var array $request
     */
    protected $request;
    /**
     * @var array $response
     */
    protected $response;
    /**
     * @var \yii\di\Container $container
     */
    public $container;

    /**
     * @SWG\Post(path="/integration/callback/load-dictionary",
     *     tags={"Callback"},
     *     summary="Загрузка справочников с помощью коллбека",
     *     description="Загрузка справочников с помощью коллбека",
     *     produces={"application/xml"},
     *     @SWG\Parameter(
     *         name="t",
     *         in="body",
     *         required=false,
     *         @SWG\Schema (
     *             @SWG\Property(property="user", ref="#/definitions/UserNoAuth"),
     *             @SWG\Schema(
     *                 default={{}}
     *             )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *             default={{}}
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     */
    public function actionLoadDictionary()
    {

        $task_id = Yii::$app->getRequest()->getQueryParam(AbstractSyncFactory::CALLBACK_TASK_IDENTIFIER);
        if (!$task_id) {
            SyncLog::trace('Required variable "task_id" is wrong!');
            throw new BadRequestHttpException("empty_param|" . AbstractSyncFactory::CALLBACK_TASK_IDENTIFIER);
        }
        SyncLog::trace('Task_id" is valid!');


        $mcTask = OuterTask::findOne(['inner_guid' => $task_id]);
        if (!$mcTask || $mcTask->int_status_id != OuterTask::STATUS_REQUESTED) {
            SyncLog::trace('Required variable "task_id" is wrong!');
            throw new BadRequestHttpException("wrong_param|" . AbstractSyncFactory::CALLBACK_TASK_IDENTIFIER);
        }

        $this->response = $this->container->get('NoAuthWebApi')->loadDictionary($mcTask);

    }

}