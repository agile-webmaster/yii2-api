<?php

namespace api_web\classes;

use api_web\helpers\WebApiHelper;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\data\Pagination;
use common\models\Request;
use yii\helpers\ArrayHelper;
use api_web\components\Notice;
use api_web\components\WebApi;
use common\models\Organization;
use yii\data\ArrayDataProvider;
use yii\data\ActiveDataProvider;
use common\models\RequestCallback;
use yii\web\BadRequestHttpException;
use api_web\exceptions\ValidationException;

class RequestWebApi extends WebApi
{
    /**
     * Список заявок для ресторана
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function getListClient(array $post)
    {
        if ($this->user->organization->type_id !== Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('Раздел доступен только для ресторанов...');
        }

        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $query = Request::find();
        $query->where(['rest_org_id' => $this->user->organization->id]);

        if (isset($post['search'])) {
            /**
             * Фильтр по статусу
             */
            if (isset($post['search']['status'])) {
                $query->andWhere(['active_status' => (int)$post['search']['status']]);
            }

            if (isset($post['search']['name'])) {
                $query->andWhere(['LIKE', 'product', $post['search']['name']]);
            }
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $query->orderBy(['created_at' => SORT_DESC])->all()
        ]);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        $models = $dataProvider->models;

        $result = [];
        foreach ($models as $model) {
            $result[] = $this->prepareRequest($model);
        }

        $return = [
            'result' => $result,
            'pagination' => [
                'page' => ($dataProvider->pagination->page + 1),
                'page_size' => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

        return $return;
    }

    /**
     * Список заявок для поставщика
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function getListVendor(array $post)
    {
        if ($this->user->organization->type_id !== Organization::TYPE_SUPPLIER) {
            throw new BadRequestHttpException('Раздел доступен только для поставщиков...');
        }

        $organization = $this->user->organization;
        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $query = $this->getVendorRequestsQuery()->andWhere(['active_status' => Request::ACTIVE]);

        /**
         * только мои заявки, на которые откликнулся
         */
        if (isset($post['my_only']) && $post['my_only'] == true) {
            $query->andWhere(['responsible_supp_org_id' => (int)$organization->id]);
        }

        if (isset($post['search'])) {
            /**
             * Фильтр по Категории
             */
            if (!empty($post['search']['category'])) {
                $query->andWhere(['category' => (int)$post['search']['category']]);
            }
            /**
             * поиск по продукту
             */
            if (!empty($post['search']['product'])) {
                $query->andWhere(['like', 'product', $post['search']['product']]);
            }
            /**
             * только срочные заявки
             */
            if (isset($post['search']['urgent'])) {
                $urgent = (int)$post['search']['urgent'];
                if ($urgent === 1) {
                    $query->andWhere(['rush_order' => 1]);
                } else {
                    $query->andWhere(['OR', ['=', 'rush_order', 0], ['is', 'rush_order', null]]);
                }
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        $result = [];
        foreach ($dataProvider->models as $model) {
            $result[] = $this->prepareRequest($model);
        }

        $return = [
            'result' => $result,
            'pagination' => [
                'page' => ($dataProvider->pagination->page + 1),
                'page_size' => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

        return $return;
    }

    /**
     * Список категорий
     * @return array
     */
    public function getCategoryList()
    {
        $result = [];
        $category = ArrayHelper::map(\common\models\MpCategory::find()->where(['parent' => null])->orderBy('name')->all(), 'id', 'name');

        if (!empty($category)) {
            foreach ($category as $key => $item) {
                $result[] = ['id' => $key, 'name' => \Yii::t('app', $item)];
            }
        }

        return $result;
    }

    /**
     * Список откликов на заявку
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function getCallbackList(array $post)
    {
        if ($this->user->organization->type_id !== Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('Вы не можете смотреть предложения, могут только рестораны...');
        }

        if (empty($post['request_id'])) {
            throw new BadRequestHttpException('empty_param|request_id');
        }

        $model = Request::find()->where(['id' => (int)$post['request_id']])->one();
        if (empty($model)) {
            throw new BadRequestHttpException('request_not_found');
        }

        $this->checkAccess($model);

        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $dataProvider = new ArrayDataProvider([
            'allModels' => $model->requestCallbacks
        ]);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        $result = [];
        foreach ($dataProvider->models as $model) {
            $result[] = $this->getCallback($model->id);
        }

        $return = [
            'result' => $result,
            'pagination' => [
                'page' => ($dataProvider->pagination->page + 1),
                'page_size' => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

        return $return;
    }

    /**
     * Карточка заявки
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function getRequest(array $post)
    {
        if (empty($post['request_id'])) {
            throw new BadRequestHttpException('empty_param|request_id');
        }

        $model = Request::find()->where(['id' => (int)$post['request_id']])->one();
        if (empty($model)) {
            throw new BadRequestHttpException('request_not_found');
        }

        $this->checkAccess($model);

        return $this->prepareRequest($model);
    }

    /**
     * Создание заявки
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function create(array $post)
    {
        if ($this->user->organization->type_id !== Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('Вы не можете создавать заявки, могут только рестораны...');
        }

        if (empty($post['category_id'])) {
            throw new BadRequestHttpException('empty_param|category_id');
        }

        if (empty($post['product'])) {
            throw new BadRequestHttpException('empty_param|product');
        }

        if (empty($post['amount'])) {
            throw new BadRequestHttpException('empty_param|amount');
        }

        $issetCategory = false;
        $category = $this->getCategoryList();
        foreach ($category as $item) {
            if ($item['id'] === $post['category_id']) {
                $issetCategory = true;
            }
        }

        if ($issetCategory === false) {
            throw new BadRequestHttpException('category_not_found');
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $model = new Request();
            $model->category = (int)$post['category_id'];
            $model->product = (string)$post['product'];
            $model->amount = (string)$post['amount'];
            $model->regular = (isset($post['regular']) ? (string)$post['regular'] : "1");
            $model->payment_method = (int)$post['payment_type'] ?? 1;
            $model->rush_order = (int)$post['urgent'] ?? 0;
            $model->comment = (string)$post['comment'] ?? '';
            $model->deferment_payment = (string)$post['deferment_payment'] ?? '';
            $model->rest_org_id = $this->user->organization->id;
            $model->rest_user_id = $this->user->id;
            $model->active_status = 1;

            if (!$model->validate()) {
                throw new ValidationException($model->getFirstErrors());
            }

            if (!$model->save()) {
                throw new ValidationException($model->getFirstErrors());
            }
            $transaction->commit();
            return $this->prepareRequest($model);
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Снять заявку
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function close(array $post)
    {
        if (empty($post['request_id'])) {
            throw new BadRequestHttpException('empty_param|request_id');
        }

        $model = Request::findOne((int)$post['request_id']);
        if (empty($model)) {
            throw new BadRequestHttpException('request_not_found');
        }

        $this->checkAccess($model);

        $model->active_status = Request::INACTIVE;
        $model->save();
        return $this->prepareRequest($model);
    }

    /**
     * Добавить предложение
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function addCallback(array $post)
    {
        if ($this->user->organization->type_id !== Organization::TYPE_SUPPLIER) {
            throw new BadRequestHttpException('Вы не можете отправить предложение, доступно только поставщикам...');
        }

        if (empty($post['request_id'])) {
            throw new BadRequestHttpException('empty_param|request_id');
        }

        if (empty($post['price'])) {
            throw new BadRequestHttpException('empty_param|price');
        }

        $request = Request::findOne((int)$post['request_id']);
        if (empty($request)) {
            throw new BadRequestHttpException('Not found request');
        }

        if ($request->active_status == 0) {
            throw new BadRequestHttpException('Request not active');
        }

        $this->checkAccess($request);

        $model = RequestCallback::find()->where(['request_id' => $request->id])
            ->andWhere(['supp_org_id' => $this->user->organization->id]);

        if ($model->exists()) {
            throw new BadRequestHttpException('Вы уже оставили отклик');
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $model = new RequestCallback();
            $model->request_id = $request->id;
            $model->supp_org_id = $this->user->organization->id;
            $model->supp_user_id = $this->user->id;
            $model->price = $post['price'];
            $model->comment = $post['comment'] ?? '';

            if ($model->validate() && $model->save()) {
                //Отправляем уведомления
                Notice::init('Request')->addCallback($request, $this->user);
                $transaction->commit();
                $model->refresh();
                return $this->prepareRequestCallback($model);
            } else {
                throw new ValidationException($model->getFirstErrors());
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function setContractor(array $post)
    {
        if ($this->user->organization->type_id !== Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('Вы не ресторан, проходите дальше...');
        }

        if (empty($post['request_id'])) {
            throw new BadRequestHttpException('empty_param|request_id');
        }

        if (empty($post['callback_id'])) {
            throw new BadRequestHttpException('empty_param|callback_id');
        }

        $request = Request::findOne((int)$post['request_id']);
        if (empty($request)) {
            throw new BadRequestHttpException('Not found request');
        }

        $this->checkAccess($request);

        $callback = RequestCallback::find()->where(['request_id' => $request->id, 'id' => (int)$post['callback_id']])->one();
        if (empty($callback)) {
            throw new BadRequestHttpException('Not found RequestCallback');
        }

        if ($request->responsible_supp_org_id == $callback->supp_org_id) {
            throw new BadRequestHttpException('Вы уже установлены исполнителем.');
        }

        if (!empty($request->responsible_supp_org_id)) {
            throw new BadRequestHttpException('На эту заявку уже назначен исполнитель.');
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $request->responsible_supp_org_id = $callback->supp_org_id;
            $request->save();
            $request->refresh();
            Notice::init('Request')->setContractor($request, $callback, $this->user);
            $transaction->commit();
            return $this->prepareRequest($request);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function unsetContractor(array $post)
    {
        if ($this->user->organization->type_id !== Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('Вы не ресторан, проходите дальше...');
        }

        if (empty($post['request_id'])) {
            throw new BadRequestHttpException('empty_param|request_id');
        }

        $request = Request::findOne((int)$post['request_id']);
        if (empty($request)) {
            throw new BadRequestHttpException('Not found request');
        }

        $this->checkAccess($request);

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $request->responsible_supp_org_id = null;
            $request->save();
            $transaction->commit();
            return $this->prepareRequest($request);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Проверка на доступ к заявке
     * @param Request $model
     * @return bool
     * @throws BadRequestHttpException
     */
    private function checkAccess(Request $model)
    {
        if ($this->user->organization->type_id == Organization::TYPE_RESTAURANT) {
            if ($model->rest_org_id !== $this->user->organization->id) {
                throw new BadRequestHttpException('Вы не можете смотреть чужие заявки.');
            }
        }
        if ($this->user->organization->type_id == Organization::TYPE_SUPPLIER) {
            $requests = ArrayHelper::map($this->getVendorRequestsQuery()->all(), 'id', 'product');
            if (empty($requests[$model->id])) {
                throw new BadRequestHttpException('Вы не можете видеть эту заявку, она вне зоны вашей доставки.');
            }

            if ($model->active_status == Request::INACTIVE) {
                throw new BadRequestHttpException('Заявка закрыта.');
            }
        }
        return true;
    }

    /**
     * @return ActiveQuery
     * @throws BadRequestHttpException
     */
    private function getVendorRequestsQuery()
    {
        $organization = $this->user->organization;
        $query = Request::find()->joinWith('client')->orderBy('id DESC');
        //Массив в доставками
        $deliveryRegions = $organization->deliveryRegionAsArray;
        //Доступные для доставки регионы
        if (!empty($deliveryRegions['allow'])) {
            foreach ($deliveryRegions['allow'] as $row) {
                if (!empty($row['administrative_area_level_1']) && !empty($row['locality'])) {
                    $p = $row['administrative_area_level_1'] . $row['locality'];
                    $query->orWhere('CONCAT(`administrative_area_level_1`, `locality`) = :p', [':p' => $p]);
                } elseif ((empty($row['administrative_area_level_1']) || $row['administrative_area_level_1'] == 'undefined') && !empty($row['locality'])) {
                    $query->orWhere(['=', 'locality', $row['locality']]);
                } elseif (!empty($row['administrative_area_level_1']) && empty($row['locality'])) {
                    $query->orWhere(['=', 'administrative_area_level_1', $row['administrative_area_level_1']]);
                }
            }
        } else {
            throw new BadRequestHttpException('Необходимо установить регионы доставки.');
        }

        //Условия для исключения доставки с регионов
        if (!empty($deliveryRegions['exclude'])) {
            if (!empty($deliveryRegions['exclude'])) {
                foreach ($deliveryRegions['exclude'] as $row) {
                    if (!empty($row['administrative_area_level_1']) && !empty($row['locality'])) {
                        $p = $row['administrative_area_level_1'] . $row['locality'];
                        $query->andWhere('CONCAT(`administrative_area_level_1`, `locality`) <> :s', [':s' => $p]);
                    } elseif ((empty($row['administrative_area_level_1']) || $row['administrative_area_level_1'] == 'undefined') && !empty($row['locality'])) {
                        $query->andWhere(['!=', 'locality', $row['locality']]);
                    } elseif (!empty($row['administrative_area_level_1']) && empty($row['locality'])) {
                        $query->andWhere(['!=', 'administrative_area_level_1', $row['administrative_area_level_1']]);
                    }
                }
            }
        }

        $query->andWhere(['>=', 'end', new \yii\db\Expression('NOW()')]);

        return $query;
    }

    /**
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     */
    private function getCallback($id)
    {
        $model = RequestCallback::findOne($id);
        if (empty($model)) {
            throw new BadRequestHttpException('Not found RequestCallback::id = ' . $id);
        }

        return $this->prepareRequestCallback($model);
    }

    /**
     * Информация по заявке
     * @param Request $model
     * @return array
     */
    private function prepareRequest(Request $model)
    {
        return [
            'id' => (int)$model->id,
            "name" => $model->product,
            "status" => (int)$model->active_status,
            "created_at" => \Yii::$app->formatter->asDate($model->created_at, 'dd.MM.yyyy HH:mm:ss'),
            "end_at" => !empty($model->end) ? \Yii::$app->formatter->asDate($model->end, 'dd.MM.yyyy HH:mm:ss') : null,
            "category" => $model->categoryName->name,
            "category_id" => (int)$model->category,
            "amount" => $model->amount,
            "comment" => $model->comment,
            "client" => WebApiHelper::prepareOrganization($model->client),
            "vendor" => WebApiHelper::prepareOrganization($model->vendor) ?? null,
            "hits" => (int)$model->count_views ?? 0,
            "count_callback" => (int)$model->countCallback ?? 0,
            "urgent" => (int)$model->rush_order ?? 0,
            "payment_method" => $model->payment_method,
            "deferment_payment" => $model->deferment_payment,
            "regular" => (int)$model->regular,
            "regular_name" => $model->regularName
        ];
    }

    /**
     * Информация о предложении
     * @param RequestCallback $model
     * @return array
     */
    private function prepareRequestCallback(RequestCallback $model)
    {
        return [
            'id' => (int)$model->id,
            "request_id" => (int)$model->request_id,
            "client" => WebApiHelper::prepareOrganization($model->request->client),
            "vendor" => WebApiHelper::prepareOrganization($model->organization),
            "price" => round($model->price, 2),
            "comment" => $model->comment,
            "created_at" => \Yii::$app->formatter->asDate($model->created_at, 'dd.MM.yyyy HH:mm:ss'),
            "updated_at" => \Yii::$app->formatter->asDate($model->updated_at, 'dd.MM.yyyy HH:mm:ss'),
        ];
    }
}