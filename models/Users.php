<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property string $email
 * @property string $name
 * @property string $password_hash
 * @property int|null $dt_add
 * @property string|null $user_img
 * @property string|null $quote
 * @property string|null $country
 * @property string $city
 * @property string|null $age
 * @property string|null $phone
 * @property string|null $telegram
 * @property int|null $status
 * @property string|null $user_status
 * @property int|null $answer_orders
 */
class Users extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email', 'name', 'password_hash', 'city'], 'required'],
            [['dt_add', 'status', 'answer_orders'], 'integer'],
            [['email', 'name', 'password_hash', 'user_img', 'country', 'city', 'age', 'phone', 'telegram', 'user_status'], 'string', 'max' => 255],
            [['quote'], 'string', 'max' => 1000],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'email' => Yii::t('app', 'Email'),
            'name' => Yii::t('app', 'Name'),
            'password_hash' => Yii::t('app', 'Password Hash'),
            'dt_add' => Yii::t('app', 'Dt Add'),
            'user_img' => Yii::t('app', 'User Img'),
            'quote' => Yii::t('app', 'Quote'),
            'country' => Yii::t('app', 'Country'),
            'city' => Yii::t('app', 'City'),
            'age' => Yii::t('app', 'Age'),
            'phone' => Yii::t('app', 'Phone'),
            'telegram' => Yii::t('app', 'Telegram'),
            'status' => Yii::t('app', 'Status'),
            'user_status' => Yii::t('app', 'User Status'),
            'answer_orders' => Yii::t('app', 'Answer Orders'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return Users the active query used by this AR class.
     */

    public function getAvgRating()
    {
        static $rating = null;

        if (is_null($rating) && count($this->replies) > 0) {
            $ratings = [];
            foreach ($this->replies as $reply) {
                $ratings[] = $reply->rate;
            }

            $rating = array_sum($ratings) / count($ratings);
        }
        return $rating;
    }

    public function getUserAvgRating()
    {
        static $rating = null;

        if (is_null($rating)) {
            $ratings = [];
            foreach ($this->executorReplies as $reply) {
                $ratings[] = $reply->rate;
            }

            $rating = array_sum($ratings) / count($ratings);


        }
        return $rating;
    }

    public function getReplies()
    {
        return $this->hasMany(UserReplies::class, ['user_id' => 'id']);
    }

    public function getDoneTasks()
    {
        return $this->hasMany(Tasks::class, ['user_id' => 'id'])->andWhere(['status' => 1]);
    }

    public function getFailedTasks()
    {
        return $this->hasMany(Tasks::class, ['user_id' => 'id'])->andWhere(['status' => 0]);
    }

    public function getExecutorReplies()
    {
        return $this->hasMany(UserReplies::class, ['executor_id' => 'id']);
    }

    public function getUserRating()
    {
        // отношение к новой таблице отзывов пользователя
        return $this->hasMany(UserRating::class, ['user_id' => 'id']);
    }

    public function getTags()
    {
        return $this->hasMany(TagsAttributes::class, ['id' => 'id'])->viaTable('tags_attribution', ['user_id' => 'id']);
    }


    public function getAllRepliesForUsers()
    {
        return $this->hasMany(TasksReplies::class, ['user_id' => 'id'])->viaTable('replies_links', ['replies_id' => 'id'])->where(['entity' => 'user']);
    }

    public function getUser()
    {
        return $this->hasOne(UserReplies::class, ['executor_id' => 'id']);
    }

    public function getRating()
    {
        $id = \Yii::$app->request->get('id');

        /*  $rating = Yii::$app->db->createCommand("
          SELECT * FROM (SELECT *, (@position:=@position+1) as rate
          FROM (SELECT executor_id,
          SUM(rate) / COUNT(rate) as pts
          FROM user_replies, (SELECT @position:=0) as a
          GROUP BY executor_id
          ORDER BY pts DESC) AS subselect)
          as general WHERE  executor_id = $id")->queryOne();
  */
        $rating = '';
        $cache = Yii::$app->cache;
// Формируем ключ
        $key = 'rating';
// Пробуем извлечь категории из кэша.
        $rating = $cache->get($rating);
        if ($rating === false) {
            //Если $categories нет в кэше, вычисляем заново
            $rating = Yii::$app->db->createCommand("
        SELECT * FROM (SELECT *, (@position:=@position+1) as rate 
        FROM (SELECT executor_id,
        SUM(rate) / COUNT(rate) as pts
        FROM user_replies, (SELECT @position:=0) as a
        GROUP BY executor_id
        ORDER BY pts DESC) AS subselect) 
        as general WHERE  executor_id = $id")->queryOne();
            // Сохраняем значение $categories в кэше по ключу. Данные можно получить в следующий раз.
            $cache->set($key, $rating);
        }
        return $rating;

    }
}
