<?php

/**
 * Class SystemController
 * @package api\modules\v1\modules\web\controllers
 * @createdBy Basil A Konakov
 * @createdAt 2017-04-03
 * @author YYY
 * @module WEB-API
 * @version 2.0
 */

namespace api_web\controllers;

use api_web\components\WebApiController;

/**
 * Class SystemController
 * @package api_web\controllers
 */
class SystemController extends WebApiController
{

    /**
     * @SWG\Post(path="/system/datetime",
     *     tags={"System"},
     *     summary="Параметры работы сервера со временем",
     *     description="Вывод localtime с помощью функций PHP",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=false,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/UserNoAuth"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={{}}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *             default={
     *                 "date_default_timezone_get()": "var_dump()",
     *                 "time": "var_dump()",
     *                 "microtime(1)": "var_dump()",
     *                 "localtime()": "var_dump()",
     *                 "getdate()": "var_dump()",
     *                 "gmdate('Y-m-d H:i:s')": "var_dump()",
     *                 "date('Y-m-d H:i:s')": "var_dump()"
     *             }
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
    public function actionDatetime()
    {
        $this->response = $this->container->get('SystemWebApi')->datetime();
    }

}