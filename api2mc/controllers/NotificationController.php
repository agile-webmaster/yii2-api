<?php

namespace api_web\controllers;

use api_web\components\WebApiController;

class NotificationController extends WebApiController
{
    /**
     * @SWG\Post(path="/notification/get",
     *     tags={"Notification"},
     *     summary="Получить уведомление",
     *     description="Получить уведомление",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  ref="#/definitions/User"
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                               "id":"5b167af001d5d"
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default=
     *    {
     *          "result": "123"
     *     }
     *          ),
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
    public function actionGet()
    {
        $this->response = $this->container->get('NotificationWebApi')->get($this->request);
    }

    /**
     * @SWG\Post(path="/notification/push",
     *     tags={"Notification"},
     *     summary="Добавить уведомление",
     *     description="Добавить уведомление",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  ref="#/definitions/User"
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                               "body":"Тест уведомление"
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default=
     *    {
     *          "result":1
     *     }
     *          ),
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
    public function actionPush()
    {
        $this->response = $this->container->get('NotificationWebApi')->push($this->request);
    }

    /**
     * @SWG\Post(path="/notification/delete",
     *     tags={"Notification"},
     *     summary="Удаление уведомления",
     *     description="Удаление уведомления",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  ref="#/definitions/User"
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                               "id":{"5b167af001d5d","5b167af001d5c","5b167af001d5f"}
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default=
     *    {
     *          "result":1
     *     }
     *          ),
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
    public function actionDelete()
    {
        $this->response = $this->container->get('NotificationWebApi')->delete($this->request);
    }

    /**
     * @SWG\Post(path="/notification/push-any-user",
     *     tags={"Notification"},
     *     summary="Добавить уведомление любому пользователю",
     *     description="Добавить уведомление любому пользователю",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  ref="#/definitions/User"
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                                "user_id":1,
     *                               "body":"Тест уведомление"
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default=
     *    {
     *          "result":1
     *     }
     *          ),
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
    public function actionPushAnyUser()
    {
        $this->response = $this->container->get('NotificationWebApi')->pushAnyUser($this->request);
    }

}