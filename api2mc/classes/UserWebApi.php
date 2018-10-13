<?php

namespace api_web\classes;

use api_web\helpers\WebApiHelper;
use common\models\RelationSuppRest;
use common\models\RelationUserOrganization;
use common\models\Role;
use api_web\models\User;
use common\models\Profile;
use common\models\SmsCodeChangeMobile;
use common\models\UserToken;
use api_web\components\Notice;
use common\models\RelationSuppRestPotential;
use common\models\Organization;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use api_web\exceptions\ValidationException;

/**
 * Class UserWebApi
 *
 * @package api_web\classes
 */
class UserWebApi extends \api_web\components\WebApi
{

    /**
     * Информация о пользователе
     *
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function get($post)
    {
        if (!empty($post['email'])) {
            $model = User::findOne(['email' => $post['email']]);
        } else {
            $user_id = $post['id'] ?? $this->user->id;
            $model = User::findOne($user_id);
        }

        if (empty($model)) {
            throw new BadRequestHttpException('user_not_found');
        }

        return [
            'id'      => $model->id,
            'email'   => $model->email,
            'phone'   => $model->profile->phone,
            'name'    => $model->profile->full_name,
            'role_id' => $model->role->id,
            'role'    => $model->role->name,
        ];
    }

    /**
     * Часовой пояс пользователя
     *
     * @return array
     */
    public function getGmt()
    {
        return ['GMT' => \Yii::$app->request->headers->get('GMT') ?? 0];
    }

    /**
     * Создание пользователя
     *
     * @param array $post
     * @return string
     * @throws ValidationException
     * @throws \Exception
     */
    public function create(array $post)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {

            $organization = new Organization (["scenario" => "register"]);
            $organization->load($post, 'organization');
            $organization->is_allowed_for_franchisee = 0;

            if ($organization->rating == null or empty($organization->rating) or empty(trim($organization->rating))) {
                $organization->setAttribute('rating', 0);
            }

            if (!$organization->validate()) {
                throw new ValidationException($organization->getFirstErrors());
            }
            $organization->save();

            $user = $this->createUser($post, Role::getManagerRole($organization->type_id));
            $user->setOrganization($organization, true);
            $user->setRelationUserOrganization($organization->id, $user->role_id);
            $profile = $this->createProfile($post, $user);

            $userToken = UserToken::generate($user->id, UserToken::TYPE_EMAIL_ACTIVATE);
            Notice::init('User')->sendSmsCodeToActivate($userToken->getAttribute('pin'), $profile->phone);
            $transaction->commit();
            return $user->id;
        } catch (ValidationException $e) {
            $transaction->rollBack();
            throw new ValidationException($e->validation);
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Создание пользователя
     *
     * @param array   $post
     * @param integer $role_id
     * @return User
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function createUser(array $post, $role_id, $status = null)
    {
        if (User::findOne(['email' => $post['user']['email']])) {
            throw new BadRequestHttpException('Данный Email уже присутствует в системе.');
        }

        $post['user']['newPassword'] = $post['user']['password'];
        unset($post['user']['password']);

        $user = new User(["scenario" => "register"]);
        $user->load($post, 'user');
        if (!$user->validate()) {
            throw new ValidationException($user->getFirstErrors());
        }
        $user->setRegisterAttributes($role_id, $status);
        $user->save();
        return $user;
    }

    /**
     * Создание профиля пользователя
     *
     * @param array $post
     * @param User  $user
     * @return Profile
     * @throws ValidationException
     */
    public function createProfile(array $post, User $user)
    {
        $phone = preg_replace('#(\s|\(|\)|-)#', '', $post['profile']['phone']);
        if (mb_substr($phone, 0, 1) == '8') {
            $phone = preg_replace('#^8(\d.+?)#', '+7$1', $phone);
        }

        if (!preg_match('#^(\+\d{1,2}|8)\d{3}\d{7,10}$#', $phone)) {
            throw new ValidationException(['phone' => 'Bad format. (+79112223344)']);
        }

        $profile = new Profile (["scenario" => "register"]);
        $profile->load($post, 'profile');
        if (!$profile->validate()) {
            throw new ValidationException($profile->getFirstErrors());
        }
        $profile->setUser($user->id)->save();
        return $profile;
    }

    /**
     * Повторная отправка СМС с кодом активации пользователя
     *
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function registrationRepeatSms($post)
    {
        WebApiHelper::clearRequest($post);

        if (!isset($post['user_id'])) {
            throw new BadRequestHttpException('empty_param|user_id');
        }

        $model = User::findOne($post['user_id']);
        if (empty($model)) {
            throw new BadRequestHttpException('user_not_found');
        }

        $userToken = UserToken::findByUser($model->id, UserToken::TYPE_EMAIL_ACTIVATE);

        if (empty($userToken)) {
            $userToken = UserToken::generate($model->id, UserToken::TYPE_EMAIL_ACTIVATE, 'attempt|1|' . gmdate("Y-m-d H:i:s"));
        } else {
            if (!empty($userToken->data)) {
                //Какая попытка
                $attempt = explode('|', $userToken->data)[1] ?? 1;
                if ($attempt >= 10) {
                    //Дата последней СМС
                    $update_date = explode('|', $userToken->data)[2] ?? gmdate('Y-m-d H:i:s');
                    //Сколько прошло времени
                    $wait_time = round(strtotime(gmdate('Y-m-d H:i:s')) - strtotime($update_date));
                    if ($wait_time < 300 && $wait_time > 0) {
                        throw new BadRequestHttpException('wait_sms_send|' . (300 - (int)$wait_time));
                    }
                    $attempt = 0;
                }
                $data = implode('|', [
                    'attempt',
                    ($attempt + 1),
                    gmdate("Y-m-d H:i:s")
                ]);
                $userToken = UserToken::generate($model->id, UserToken::TYPE_EMAIL_ACTIVATE, $data);
            }
        }

        Notice::init('User')->sendSmsCodeToActivate($userToken->getAttribute('pin'), $model->profile->phone);

        return ['result' => 1];
    }

    /**
     * Подтверждение регистрации
     *
     * @param array $post
     * @return string
     * @throws \Exception
     */
    public function confirm(array $post)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $user_id = (int)trim($post['user_id']);
            if (empty($user_id)) {
                throw new BadRequestHttpException('empty_param|user_id');
            }

            $code = (int)trim($post['code']);
            if (empty($code)) {
                throw new BadRequestHttpException('empty_param|code');
            }

            $userToken = UserToken::findByPIN($code, [UserToken::TYPE_EMAIL_ACTIVATE]);
            if (!$userToken || ($userToken->user_id !== $user_id)) {
                throw new BadRequestHttpException(\Yii::t('app', 'api.modules.v1.modules.mobile.controllers.wrong_code'));
            }

            $user = User::findOne($user_id);
            $user->setAttribute('status', User::STATUS_ACTIVE);
            $user->save();
            Notice::init('User')->sendEmailWelcome($user);
            $userToken->delete();
            $transaction->commit();
            return $user->access_token;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Выбор бизнеса
     *
     * @param array $post
     * @param array $post
     * @return bool
     * @throws \Exception
     */
    public function setOrganization(array $post)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {

            if (!isset($post['organization_id'])) {
                throw new BadRequestHttpException('empty_param|organization_id');
            }

            $organization = Organization::findOne(['id' => $post['organization_id']]);
            if (empty($organization)) {
                throw new BadRequestHttpException('Нет организации с таким id');
            }

            if (!$this->user->isAllowOrganization($organization->id)) {
                throw new BadRequestHttpException('Нет прав переключиться на эту организацию');
            }

            $roleID = RelationUserOrganization::getRelationRole($organization->id, $this->user->id);

            if ($roleID != null) {
                if (!in_array($this->user->role_id, [Role::ROLE_ADMIN, Role::ROLE_XXX_MANAGER])) {
                    if ($organization->type_id == Organization::TYPE_RESTAURANT) {
                        $this->user->role_id = $roleID ?? Role::ROLE_RESTAURANT_MANAGER;
                    }
                    if ($organization->type_id == Organization::TYPE_SUPPLIER) {
                        $this->user->role_id = $roleID ?? Role::ROLE_SUPPLIER_MANAGER;
                    }
                }
                $this->user->organization_id = $organization->id;
            } elseif (in_array($this->user->role_id, Role::getFranchiseeEditorRoles())) {
                $this->user->organization_id = $organization->id;
            } else {
                throw new \Exception('access denied.');
            }

            if (!$this->user->save()) {
                throw new ValidationException($this->user->getFirstErrors());
            }

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Список бизнесов пользователя
     *
     * @return array
     * @throws BadRequestHttpException
     */
    public function getAllOrganization($searchString = null, $showEmpty = false): array
    {
        $list_organisation = $this->user->getAllOrganization($searchString);
        if (empty($list_organisation) && !$showEmpty) {
            throw new BadRequestHttpException('Нет доступных организаций');
        }

        $result = [];
        foreach ($list_organisation as $item) {
            $model = Organization::findOne($item['id']);
            $result[] = WebApiHelper::prepareOrganization($model);
        }

        return $result;
    }

    /**
     * Список поставщиков пользователя
     *
     * @param array $post
     * @return array
     */
    public function getVendors(array $post)
    {
        $sort = (isset($post['sort']) ? $post['sort'] : null);
        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $currentOrganization = $this->user->organization;
        $searchModel = new \common\models\search\VendorSearch();

        $dataProvider = $searchModel->search([], $currentOrganization->id);
        $dataProvider->pagination->setPage($page - 1);
        $dataProvider->pagination->pageSize = $pageSize;

        /**
         * Поиск по статусу поставщика
         */
        if (isset($post['search']['status'])) {
            switch ($post['search']['status']) {
                case 1:
                    $addWhere = ['invite' => 1, 'u.status' => 1];
                    break;
                case 2:
                    $addWhere = ['invite' => 1, 'u.status' => 0];
                    break;
                case 3:
                    $addWhere = ['or',
                        ['invite' => 0, 'u.status' => 1],
                        ['invite' => 0, 'u.status' => 0]
                    ];
                    break;
            }
            if (isset($addWhere)) {
                $dataProvider->query->andFilterWhere($addWhere);
            }
        }

        /**
         * Поиск по наименованию
         */
        if (isset($post['search']['name'])) {
            $dataProvider->query->andFilterWhere(['like', 'u.vendor_name', $post['search']['name']]);
        }

        //Поиск по адресу
        if (isset($post['search']['location'])) {
            if (strstr($post['search']['location'], ':') !== false) {
                $location = explode(':', $post['search']['location']);
                if (is_array($location)) {
                    if (isset($location[0])) {
                        $dataProvider->query->andFilterWhere(['u.country' => $location[0]]);
                    }
                    if (isset($location[1])) {
                        $dataProvider->query->andFilterWhere(['u.locality' => $location[1]]);
                    }
                }
            } else {
                $dataProvider->query->andFilterWhere(
                    ['or',
                        ['u.country' => $post['search']['location']],
                        ['u.locality' => $post['search']['location']]
                    ]
                );
            }
        }

        //$dataProvider->query->andWhere('1=0');
        //Ответ
        $return = [
            'headers'    => [],
            'vendors'    => [],
            'pagination' => [
                'page'       => $page,
                'page_size'  => $pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ],
            'sort'       => $sort
        ];

        //Сортировка
        if (isset($post['sort'])) {

            $field = $post['sort'];
            $sort = 'ASC';

            if (strstr($post['sort'], '-') !== false) {
                $field = str_replace('-', '', $field);
                $sort = 'DESC';
            }

            if ($field == 'name') {
                $field = 'vendor_name ' . $sort;
            }

            if ($field == 'address') {
                //$field = 'organization.locality ' . $sort;
            }

            if ($field == 'status') {
                switch ($sort) {
                    case 'DESC':
                        $sort = 'ASC';
                        break;
                    case 'ASC':
                        $sort = 'DESC';
                        break;
                }
                $field = "invite {$sort}, `status` {$sort}";
            }

            $dataProvider->query->orderBy($field);
        }
        //Данные для ответа
        foreach ($dataProvider->models as $model) {
            $return['vendors'][] = $this->prepareVendor($model);
        }
        //Названия полей
        if (isset($return['vendors'][0])) {
            foreach (array_keys($return['vendors'][0]) as $key) {
                $return['headers'][$key] = (new Organization())->getAttributeLabel($key);
            }
        }

        return $return;
    }

    /**
     * Список статусов поставщиков
     *
     * @return array
     */
    public function getVendorStatusList()
    {
        return [
            1 => \Yii::t('message', 'frontend.views.client.suppliers.partner'),
            2 => \Yii::t('message', 'frontend.views.client.suppliers.catalog_not_set'),
            3 => \Yii::t('message', 'frontend.views.client.suppliers.send_invite'),
        ];
    }

    /**
     * Список географического расположения поставщиков ресторана
     *
     * @return array
     */
    public function getVendorLocationList()
    {
        $currentOrganization = $this->user->organization;
        $searchModel = new \common\models\search\VendorSearch();
        $dataProvider = $searchModel->search([], $currentOrganization->id);
        $dataProvider->pagination->setPage(0);
        $dataProvider->pagination->pageSize = 1000;

        $return = [];
        $vendor_ids = [];

        $models = $dataProvider->getModels();
        if (!empty($models)) {
            foreach ($models as $model) {
                $vendor_ids[] = $model->supp_org_id;
            }

            $vendor_ids = array_unique($vendor_ids);

            $query = new Query();
            $query->distinct();
            $query->from(Organization::tableName());
            $query->select(['country', 'locality']);
            $query->where(['in', 'id', $vendor_ids]);
            $query->andWhere('country is not null');
            $query->andWhere("country != 'undefined'");
            $query->andWhere("country != ''");
            $query->andWhere('locality is not null');
            $query->andWhere("locality != 'undefined'");
            $query->andWhere("locality != ''");
            $query->orderBy('country');

            $result = $query->all();

            if ($result) {
                foreach ($result as $row) {
                    $return[] = [
                        'title' => $row['country'] . ', ' . $row['locality'],
                        'value' => trim($row['country']) . ':' . trim($row['locality'])
                    ];
                }
            }

        }

        return $return;
    }

    /**
     * Отключить поставщика
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function removeVendor(array $post)
    {
        if (empty($post['vendor_id'])) {
            throw new BadRequestHttpException('empty_param|vendor_id');
        }

        $id = (int)$post['vendor_id'];
        $vendor = Organization::find()->where(['id' => $id])->andWhere(['type_id' => Organization::TYPE_SUPPLIER])->one();

        if (empty($vendor)) {
            throw new BadRequestHttpException('vendor_not_found');
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $where = [
                'rest_org_id' => $this->user->organization->id,
                'supp_org_id' => $vendor->id
            ];

            if (RelationSuppRest::find()->where($where)->exists() || RelationSuppRestPotential::find()->where($where)->exists()) {
                RelationSuppRest::deleteAll($where);
                RelationSuppRestPotential::deleteAll($where);
            } else {
                throw new BadRequestHttpException('Вы не работаете с этим поставщиком');
            }
            $transaction->commit();
            return ['result' => true];
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Смена пароля пользователя
     *
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function changePassword($post)
    {
        if (empty($post['password'])) {
            throw new BadRequestHttpException('empty_param|password');
        }

        if (empty($post['new_password'])) {
            throw new BadRequestHttpException('empty_param|new_password');
        }

        if (empty($post['new_password_confirm'])) {
            throw new BadRequestHttpException('empty_param|new_password_confirm');
        }

        if (!$this->user->validatePassword($post['password'])) {
            throw new BadRequestHttpException('bad_old_password');
        }

        if ($post['password'] == $post['new_password']) {
            throw new BadRequestHttpException('same_password');
        }

        $tr = \Yii::$app->db->beginTransaction();
        try {
            $this->user->scenario = 'reset';
            $this->user->newPassword = $post['new_password'];
            $this->user->newPasswordConfirm = $post['new_password_confirm'];

            if (!$this->user->validate(['newPassword'])) {
                throw new BadRequestHttpException('bad_password|' . $this->randomPassword());
            }

            if (!$this->user->validate() || !$this->user->save()) {
                throw new ValidationException($this->user->getFirstErrors());
            }

            $tr->commit();
            return ['result' => true];
        } catch (\Exception $e) {
            $tr->rollBack();
            throw $e;
        }

    }

    /**
     * Смена мобильного номера
     *
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function mobileChange($post)
    {
        WebApiHelper::clearRequest($post);

        if (empty($post['phone'])) {
            throw new BadRequestHttpException('empty_param|password');
        }

        $phone = preg_replace('#(\s|\(|\)|-)#', '', $post['phone']);
        if (mb_substr($phone, 0, 1) == '8') {
            $phone = preg_replace('#^8(\d.+?)#', '+7$1', $phone);
        }
        //Проверяем телефон
        if (!preg_match('#^(\+\d{1,2}|8)\d{3}\d{7,10}$#', $phone)) {
            throw new ValidationException(['phone' => 'bad_format_phone']);
        }

        //Проверяем код, если прилетел
        if (!empty($post['code'])) {
            if (!preg_match('#^\d{4}$#', $post['code'])) {
                throw new ValidationException(['code' => 'bad_format_code']);
            }
        }

        //Ищем модель на смену номера
        $model = SmsCodeChangeMobile::findOne(['user_id' => $this->user->id]);
        //Если нет модели, но прилетел какой то код, даем отлуп
        if (empty($model) && !empty($post['code'])) {
            throw new BadRequestHttpException('not_code_to_change_phone');
        }

        //Если нет модели
        if (empty($model)) {
            $model = new SmsCodeChangeMobile();
            $model->phone = $phone;
            $model->user_id = $this->user->id;
        }

        //Даем отлуп если он уже достал выпращивать коды
        if ($model->isNewRecord === false && $model->accessAllow() === false) {
            throw new BadRequestHttpException('wait_sms_send|' . (300 - (int)$model->wait_time));
        }

        //Если код в запросе не пришел, шлем смс и создаем запись
        if (empty($post['code'])) {
            //Если модель не новая, значит уже были попытки отправить смс
            //поэтому мы их просто наращиваем
            if ($model->isNewRecord === false) {
                $model->setAttempt();
            }
            //Генерируем код
            $model->code = rand(1111, 9999);
            //Сохраняем модель
            if ($model->validate() && $model->save()) {
                //Отправляем СМС с кодом
                \Yii::$app->sms->send('Code: ' . $model->code, $model->phone);
            } else {
                throw new ValidationException($model->getFirstErrors());
            }
        } else {
            //Проверяем код
            if ($model->checkCode($post['code'])) {
                //Меняем номер телефона, если все хорошо
                $model->changePhoneUser();
            } else {
                throw new BadRequestHttpException('bad_sms_code');
            }
        }
        return ['result' => true];
    }


    /**
     * Смена телефона неподтвержденным пользователем
     *
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function changeUnconfirmedUsersPhone($post)
    {
        WebApiHelper::clearRequest($post);

        if (empty($post['user']['id'])) {
            throw new BadRequestHttpException('empty_param|id');
        }

        if (empty($post['profile']['phone'])) {
            throw new BadRequestHttpException('empty_param|phone');
        }

        $phone = preg_replace('#(\s|\(|\)|-)#', '', $post['profile']['phone']);
        if (mb_substr($phone, 0, 1) == '8') {
            $phone = preg_replace('#^8(\d.+?)#', '+7$1', $phone);
        }
        //Проверяем телефон
        if (!preg_match('#^(\+\d{1,2}|8)\d{3}\d{7,10}$#', $phone)) {
            throw new ValidationException(['phone' => 'bad_format_phone']);
        }

        $user = User::findOne(['id' => $post['user']['id']]);
        if (!$user) {
            throw new BadRequestHttpException('no such user');
        }

        if ($user->status == User::STATUS_ACTIVE) {
            throw new BadRequestHttpException('you have no rights for this action');
        }

        $profile = Profile::findOne(['user_id' => $user->id]);
        if (!$profile) {
            throw new BadRequestHttpException('no such users profile');
        }
        $profile->phone = $post['profile']['phone'];
        $profile->save();

        return ['result' => true];
    }


    /**
     * Информация о поставщике
     *
     * @param RelationSuppRest $model
     * @return array
     */
    public function prepareVendor(RelationSuppRest $model)
    {
        $status_list = $this->getVendorStatusList();

        $locality = [
            $model->vendor->country,
            $model->vendor->administrative_area_level_1,
            $model->vendor->locality,
            $model->vendor->route
        ];

        foreach ($locality as $key => $val) {
            if (empty($val) or $val == 'undefined') {
                unset($locality[$key]);
            }
        }

        if ($model->invite == RelationSuppRest::INVITE_ON) {
            if ($model->status == RelationSuppRest::CATALOG_STATUS_ON) {
                $status = $status_list[1];
            } else {
                $status = $status_list[2];
            }
        } else {
            $status = $status_list[3];
        }

        return [
            'id'            => (int)$model->vendor->id,
            'name'          => $model->vendor->name ?? "",
            'contact_name'  => $model->vendor->contact_name ?? "",
            'inn'           => $model->vendor->inn ?? null,
            'cat_id'        => (int)$model->cat_id,
            'email'         => $model->vendor->email ?? "",
            'phone'         => $model->vendor->phone ?? "",
            'status'        => $status,
            'picture'       => $model->vendor->getPictureUrl() ?? "",
            'address'       => implode(', ', $locality),
            'rating'        => $model->vendor->rating ?? 0,
            'allow_editing' => $model->vendor->allow_editing
        ];
    }

    /**
     * Генератор случайного пароля
     *
     * @return string
     */
    private function randomPassword()
    {
        $pass = '';
        $alphabet = "a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,w,x,y,z,";
        $alphabet .= "A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,W,X,Y,Z,";
        $alphabet = explode(',', $alphabet);
        for ($i = 0; $i < 6; $i++) {
            $n = rand(0, count($alphabet) - 1);
            $pass .= $alphabet[$n];
        }
        return $pass . rand(111, 999);
    }

    /**
     * Возвращает GMT из базы, если его нет сохраняет из headers, добавляет плюс к не отрицательному таймзону
     *
     * @return string $gmt
     * */
    public function checkGMTFromDb()
    {
        $gmt = $this->getGmt()['GMT'];

        if (!empty($this->user)) {
            $model = $this->user->organization;
            if (is_null($model->gmt)) {
                $model->gmt = $gmt;
                if ($model->validate()) {
                    $model->save();
                }
            }
            $gmt = $model->gmt;
        }

        if (strpos($gmt, '-') === 0) {
            $return = str_replace('-', '+', $gmt);
        } else {
            $return = '-' . $gmt;
        }

        return $return;
    }

    /**
     * @return array
     */
    public function getUserOrganizationBusinessList()
    {
        $res = (new Query())->select(['a.id', 'a.name'])->from('organization a')
            ->leftJoin('relation_user_organization b', 'a.id = b.organization_id')
            ->where([
                'b.user_id'   => $this->user->id,
                'a.type_id'   => 1,
                'b.role_id'   => [
                    Role::ROLE_RESTAURANT_MANAGER,
                    Role::ROLE_RESTAURANT_EMPLOYEE,
                    Role::ROLE_RESTAURANT_BUYER,
                    Role::ROLE_ADMIN,
                ]
            ])->all();

        return ['result' => $res];
    }
}