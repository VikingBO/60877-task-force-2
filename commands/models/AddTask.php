<?php

namespace app\models\commands;

use app\models\Task;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Yii;
use yii\base\BaseObject;
use yii\base\Model;
use yii\db\Exception;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;
use app\models\Files;

/**
 * This is the model class for table "users".
 *
 * @property string $about_job;
 * @property string $describe_task;
 * @property int $categories;
 * @property int $budget;
 * @property string $expire_date;
 * @property string $files;
 * @property int $location;
 */
class AddTask extends Model
{
    /**
     * {@inheritdoc}
     */
    public $about_job;
    public $describe_task;
    public $categories;
    public $budget;
    public $expire_date;
    public $files;
    public $location;
    public $task_id;

    public $task;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['about_job', 'describe_task'], 'required'],
            ['about_job', 'string', 'min' => 10],
            ['describe_task', 'string', 'min' => 30, 'max' => 255],
            ['location', 'string', 'max' => 255],
            [['files'], 'file', 'maxSize' => 1024 * 1024, 'maxFiles' => 4],
            ['categories', 'exist', 'targetClass' => Category::class, 'targetAttribute' => 'id'],
            ['budget', 'integer'],
            [['expire_date'], 'date', 'format' => 'php:Y-m-d'],

        ];
    }

    public function createNewTask()
    {
        $newTask = new Task();
        $newTask->name = $this->about_job;
        $newTask->description = $this->describe_task;
        $newTask->category_id = $this->categories;
        $newTask->address = $this->location; // в $model->location null
        $newTask->budget = $this->budget;
        $newTask->expire = $this->expire_date;
        $newTask->create_at = date('Y-m-d h-m-s');
        $newTask->status = 1;
        $newTask->user_id = \Yii::$app->user->identity->id;
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!$newTask->save()) {
                throw new Exception("Can not save new task");
            }
            $this->saveFile($newTask);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        return $newTask;
    }

    public function saveFile($task_after_save)
    {
        $instances = UploadedFile::getInstances($this, 'files');
        foreach ($instances as $instance) {
            $files = new Files();
            $instance->saveAs("uploads/" . $instance->name);
            $files->tasks_id = $task_after_save->id;
            $files->files_name = $instance->name;
            if (!$files->save()) {
                throw new ServerErrorHttpException("Файл(ы) не был(и) сохранен(ы)!");
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'about_job' => 'Опишите суть работы',
            'describe_task' => 'Подробности задания',
            'categories' => 'Категории',
            'location' => 'Локация',
            'budget' => 'Бюджет',
            'expire_date' => 'Срок исполнения',
            'files' => 'Файлы',
        ];
    }


}
